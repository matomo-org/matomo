const fs = require('fs');

const SEVERITIES = ['none', 'low', 'medium', 'blocking'];

// Sentinel embedded in every Codex review body so later runs can recognise and supersede their own
// previous reviews. The preflight job in .github/workflows/codex-review.yml matches this exact
// string to deduplicate runs, so it MUST stay byte-identical to the literal there.
const CODEX_REVIEW_MARKER = 'This Codex review supersedes any previous Codex review output for this PR.';

// Unlike requiredEnv in render-review-prompt.js, this intentionally accepts an empty string: this
// script runs with `if: always()`, so a passthrough output such as PREFLIGHT_SAFETY_FAILURE can be
// an empty string when the preflight job did not complete, and that must be handled rather than
// throw. Only a genuinely unset (undefined) variable is treated as missing here.
function requiredEnv(name) {
  const value = process.env[name];
  if (value === undefined) {
    throw new Error(`Missing required environment variable: ${name}`);
  }
  return value;
}

function expectedHighestSeverity(findings) {
  if (findings.blocking > 0) {
    return 'blocking';
  }
  if (findings.medium > 0) {
    return 'medium';
  }
  if (findings.low_polish > 0) {
    return 'low';
  }
  return 'none';
}

function assertString(value, name) {
  if (typeof value !== 'string' || value.trim() === '') {
    throw new Error(`${name} must be a non-empty string`);
  }
}

function assertInteger(value, name) {
  if (!Number.isInteger(value) || value < 0) {
    throw new Error(`${name} must be a non-negative integer`);
  }
}

// Defence-in-depth re-validation of the Codex output. The codex-action already constrains the model
// to review-output.schema.json, so this mirrors that schema as a backstop in case enforcement is
// absent or changes. Keep this in sync with .github/codex/review-output.schema.json.
function validateReview(review) {
  if (!review || typeof review !== 'object' || Array.isArray(review)) {
    throw new Error('Codex output must be a JSON object');
  }

  assertString(review.review_body_markdown, 'review_body_markdown');
  // diagnostics_markdown is intentionally not rendered into the review body; it is surfaced only via
  // the uploaded codex-review-output artifact, so the PR conversation stays concise.
  assertString(review.diagnostics_markdown, 'diagnostics_markdown');
  if (!SEVERITIES.includes(review.highest_severity)) {
    throw new Error('highest_severity is invalid');
  }

  const findings = review.findings;
  if (!findings || typeof findings !== 'object' || Array.isArray(findings)) {
    throw new Error('findings must be an object');
  }
  assertInteger(findings.blocking, 'findings.blocking');
  assertInteger(findings.medium, 'findings.medium');
  assertInteger(findings.low_polish, 'findings.low_polish');

  // Treat the finding counts as authoritative and recompute highest_severity from them rather than
  // rejecting the whole review over a trivial model inconsistency. Downstream consumers
  // (reviewEventForSeverity) then use the trustworthy value.
  review.highest_severity = expectedHighestSeverity(findings);

  if (!Array.isArray(review.inline_comments)) {
    throw new Error('inline_comments must be an array');
  }
  if (!Array.isArray(review.unplaced_findings)) {
    throw new Error('unplaced_findings must be an array');
  }

  for (const [index, comment] of review.inline_comments.entries()) {
    assertString(comment.path, `inline_comments[${index}].path`);
    if (!Number.isInteger(comment.line) || comment.line < 1) {
      throw new Error(`inline_comments[${index}].line must be a positive integer`);
    }
    if (!['LEFT', 'RIGHT'].includes(comment.side)) {
      throw new Error(`inline_comments[${index}].side must be LEFT or RIGHT`);
    }
    if (!['low', 'medium', 'blocking'].includes(comment.severity)) {
      throw new Error(`inline_comments[${index}].severity is invalid`);
    }
    assertString(comment.body, `inline_comments[${index}].body`);
    // rule_source is required by the schema but may be null; it is only read optionally downstream.
    if (comment.rule_source !== null && typeof comment.rule_source !== 'string') {
      throw new Error(`inline_comments[${index}].rule_source must be a string or null`);
    }
  }

  for (const [index, finding] of review.unplaced_findings.entries()) {
    if (!['low', 'medium', 'blocking'].includes(finding.severity)) {
      throw new Error(`unplaced_findings[${index}].severity is invalid`);
    }
    assertString(finding.body, `unplaced_findings[${index}].body`);
    // path and line are nullable per the schema; the mapping step re-derives placement from them.
    if (finding.path !== null && finding.path !== undefined && typeof finding.path !== 'string') {
      throw new Error(`unplaced_findings[${index}].path must be a string or null`);
    }
    if (finding.line !== null && finding.line !== undefined
      && (!Number.isInteger(finding.line) || finding.line < 1)) {
      throw new Error(`unplaced_findings[${index}].line must be a positive integer or null`);
    }
  }
}

function readReviewOutput(path) {
  const raw = fs.readFileSync(path, 'utf8').trim();
  if (!raw) {
    throw new Error('Codex output file is empty');
  }
  return JSON.parse(raw);
}

function parsePatchLines(patch) {
  const right = new Set();
  const left = new Set();

  if (!patch) {
    return { right, left };
  }

  let oldLine = 0;
  let newLine = 0;
  for (const line of patch.split('\n')) {
    const hunk = /^@@ -(\d+)(?:,\d+)? \+(\d+)(?:,\d+)? @@/.exec(line);
    if (hunk) {
      oldLine = Number(hunk[1]);
      newLine = Number(hunk[2]);
      continue;
    }

    if (line.startsWith('+++') || line.startsWith('---') || line.startsWith('\\')) {
      continue;
    }

    if (line.startsWith('+')) {
      right.add(newLine);
      newLine += 1;
      continue;
    }

    if (line.startsWith('-')) {
      left.add(oldLine);
      oldLine += 1;
      continue;
    }

    if (line.startsWith(' ')) {
      right.add(newLine);
      left.add(oldLine);
      oldLine += 1;
      newLine += 1;
    }
  }

  return { right, left };
}

function formatFinding(finding) {
  const location = finding.path
    ? ` (${finding.path}${finding.line ? `:${finding.line}` : ''})`
    : '';
  return `- **${finding.severity}**${location}: ${finding.body}`;
}

function pluralize(count, singular, plural = `${singular}s`) {
  return count === 1 ? singular : plural;
}

function formatSeverityCounts(findings) {
  return [
    `Blocking: ${findings.blocking}`,
    `Medium: ${findings.medium}`,
    `Low / Polish: ${findings.low_polish}`,
  ].join(', ');
}

function formatSeverityBadge(severity) {
  switch (severity) {
    case 'blocking':
      return '🚫 Blocking';
    case 'medium':
      return '⚠️ Medium';
    case 'low':
      return '💬 Low / Polish';
    case 'none':
      return '✅ No findings';
    default:
      return severity;
  }
}

function buildReviewBody(review, unplaced, inlineCount) {
  const hasFindings = review.findings.blocking + review.findings.medium + review.findings.low_polish > 0;
  const lines = [
    `<!-- ${CODEX_REVIEW_MARKER} -->`,
    `## 🤖 Codex Review: ${formatSeverityBadge(review.highest_severity)}`,
    '',
    '### Summary',
    review.review_body_markdown.trim(),
    '',
    '### Findings Overview',
    '',
    '| Severity | Count |',
    '| --- | ---: |',
    `| 🚫 Blocking | ${review.findings.blocking} |`,
    `| ⚠️ Medium | ${review.findings.medium} |`,
    `| 💬 Low / Polish | ${review.findings.low_polish} |`,
  ];

  if (inlineCount > 0) {
    lines.push('', `📍 Posted ${inlineCount} inline ${pluralize(inlineCount, 'finding')}.`);
  } else if (hasFindings) {
    lines.push('', '📍 No findings could be placed inline.');
  } else {
    lines.push('', '✅ No inline findings to place.');
  }

  if (unplaced.length > 0) {
    lines.push(
      '',
      '<details>',
      '<summary>Unplaced findings</summary>',
      '',
      ...unplaced.map(formatFinding),
      '',
      '</details>'
    );
  }

  lines.push(
    '',
    '### Diagnostics',
    'Detailed review diagnostics are available in the `codex-review-output` workflow artifact.'
  );

  return `${lines.join('\n')}\n`;
}

function reviewEventForSeverity(severity) {
  // Never emit APPROVE: the verdict is produced by an LLM reading the untrusted PR diff, so the
  // workflow must not stamp a green approval it cannot guarantee. Non-blocking outcomes are posted
  // as a plain COMMENT instead.
  if (severity === 'medium' || severity === 'blocking') {
    return 'REQUEST_CHANGES';
  }
  return 'COMMENT';
}

function isDismissableCodexReview(review) {
  // Only APPROVED and CHANGES_REQUESTED reviews can be dismissed; GitHub rejects dismissing a
  // COMMENTED review with 422. A COMMENTED review does not block the PR, so there is nothing to
  // dismiss anyway.
  return review
    && review.user
    // The login of the actor behind github.token, which is what posts and therefore dismisses these
    // reviews. If the workflow ever posts under a different identity (e.g. a GitHub App) this must
    // be updated, otherwise dismissal silently stops matching.
    && review.user.login === 'github-actions[bot]'
    && ['APPROVED', 'CHANGES_REQUESTED'].includes(review.state)
    && typeof review.body === 'string'
    && review.body.includes(CODEX_REVIEW_MARKER);
}

async function createIssueComment({ github, context, body, core }) {
  try {
    await github.rest.issues.createComment({
      owner: context.repo.owner,
      repo: context.repo.repo,
      issue_number: context.payload.pull_request.number,
      body,
    });
  } catch (error) {
    if (error.status === 403) {
      core.warning('Could not post PR comment because this workflow token lacks permission.');
      return;
    }
    throw error;
  }
}

async function dismissPreviousCodexReviews({ github, context, core, runUrl }) {
  let reviews;
  try {
    reviews = await github.paginate(github.rest.pulls.listReviews, {
      owner: context.repo.owner,
      repo: context.repo.repo,
      pull_number: context.payload.pull_request.number,
      per_page: 100,
    });
  } catch (error) {
    core.warning(`Could not list previous pull request reviews: ${error.message}`);
    return;
  }

  const previousCodexReviews = reviews.filter(isDismissableCodexReview);

  for (const previousReview of previousCodexReviews) {
    try {
      await github.rest.pulls.dismissReview({
        owner: context.repo.owner,
        repo: context.repo.repo,
        pull_number: context.payload.pull_request.number,
        review_id: previousReview.id,
        message: `Superseded by Codex Review run ${runUrl}.`,
      });
      core.info(`Dismissed previous Codex review ${previousReview.id}.`);
    } catch (error) {
      if (error.status === 403 || error.status === 422) {
        core.warning(`Could not dismiss previous Codex review ${previousReview.id}: ${error.message}`);
        continue;
      }
      throw error;
    }
  }
}

module.exports = async function postReview({ github, context, core }) {
  const pr = context.payload.pull_request;
  const safetyFailure = requiredEnv('PREFLIGHT_SAFETY_FAILURE') === 'true';
  const safetyMessage = process.env.PREFLIGHT_SAFETY_MESSAGE || '';
  const skipReason = process.env.PREFLIGHT_SKIP_REASON || '';
  const skipMessage = process.env.PREFLIGHT_SKIP_MESSAGE || '';
  const codexResult = requiredEnv('CODEX_RESULT');
  const runUrl = requiredEnv('RUN_URL');

  if (safetyFailure) {
    await createIssueComment({
      github,
      context,
      core,
      body: safetyMessage || 'Codex review was not run because this PR changes reviewer automation files.',
    });
    return;
  }

  if (skipReason) {
    await createIssueComment({
      github,
      context,
      core,
      body: skipMessage || `Codex review was skipped during preflight (${skipReason}).`,
    });
    return;
  }

  if (codexResult !== 'success') {
    await createIssueComment({
      github,
      context,
      core,
      body: `Codex review failed before producing a usable review. Workflow run: ${runUrl}`,
    });
    return;
  }

  let review;
  try {
    review = readReviewOutput(requiredEnv('CODEX_OUTPUT_FILE'));
    validateReview(review);
  } catch (error) {
    await createIssueComment({
      github,
      context,
      core,
      body: `Codex review produced invalid structured output, so no approval or request-changes review was submitted. Workflow run: ${runUrl}`,
    });
    core.setFailed(error.message);
    return;
  }

  const files = await github.paginate(github.rest.pulls.listFiles, {
    owner: context.repo.owner,
    repo: context.repo.repo,
    pull_number: pr.number,
    per_page: 100,
  });

  // listFiles returns patches for at most ~300 files and omits patches for very large or binary
  // files. Inline comments targeting those paths get an empty patch here and fall through to
  // unplaced_findings below by design (see the `valid` check) -- this degradation is expected.
  const patchesByPath = new Map();
  for (const file of files) {
    patchesByPath.set(file.filename, parsePatchLines(file.patch));
  }

  const comments = [];
  const unplaced = [];
  // Mirror of the placed inline comments as plain findings, used to fold them back into the review
  // body if GitHub rejects the inline comments wholesale (see the 422 fallback below).
  const placedFindings = [];

  for (const comment of review.inline_comments) {
    const patch = patchesByPath.get(comment.path);
    const valid = patch
      && (comment.side === 'RIGHT'
        ? patch.right.has(comment.line)
        : patch.left.has(comment.line));

    if (!valid) {
      // Distinguish a patch-less path (listFiles truncation / binary / >~300 changed files) from a
      // line the model picked that simply is not part of the diff -- different root causes.
      const reason = patch
        ? `line ${comment.line} (${comment.side}) is not part of the diff`
        : 'no patch was returned for this path (large/binary file or listFiles truncation)';
      core.warning(`Demoted inline comment on ${comment.path}: ${reason}.`);
      unplaced.push({
        severity: comment.severity,
        body: comment.body,
        path: comment.path,
        line: comment.line,
      });
      continue;
    }

    comments.push({
      path: comment.path,
      line: comment.line,
      side: comment.side,
      body: comment.rule_source
        ? `${comment.body}\n\nRule source: \`${comment.rule_source}\``
        : comment.body,
    });
    placedFindings.push({
      severity: comment.severity,
      body: comment.body,
      path: comment.path,
      line: comment.line,
    });
  }

  for (const finding of review.unplaced_findings) {
    const patch = finding.path ? patchesByPath.get(finding.path) : null;
    const valid = patch && Number.isInteger(finding.line) && patch.right.has(finding.line);

    if (!valid) {
      unplaced.push(finding);
      continue;
    }

    comments.push({
      path: finding.path,
      line: finding.line,
      side: 'RIGHT',
      body: finding.body,
    });
    placedFindings.push({
      severity: finding.severity,
      body: finding.body,
      path: finding.path,
      line: finding.line,
    });
  }

  const body = buildReviewBody(review, unplaced, comments.length);
  const event = reviewEventForSeverity(review.highest_severity);

  core.info(`Codex review: placing ${comments.length} inline ${comments.length === 1 ? 'comment' : 'comments'}, ${unplaced.length} unplaced, event=${event}.`);

  try {
    await dismissPreviousCodexReviews({
      github,
      context,
      core,
      runUrl,
    });

    await github.rest.pulls.createReview({
      owner: context.repo.owner,
      repo: context.repo.repo,
      pull_number: pr.number,
      body,
      event,
      comments,
    });
  } catch (error) {
    if (error.status === 403) {
      await createIssueComment({
        github,
        context,
        core,
        body: `Codex review completed, but the workflow token could not submit a pull request review. Workflow run: ${runUrl}`,
      });
      return;
    }

    // GitHub rejects the whole review with 422 if a single inline comment lands on a line it does
    // not consider commentable. Rather than lose every finding, retry once without inline comments
    // and fold them into the body as unplaced findings.
    if (error.status === 422 && comments.length > 0) {
      core.warning(`GitHub rejected the inline comments (422): ${error.message}. Retrying without inline comments.`);
      const fallbackBody = buildReviewBody(review, [...unplaced, ...placedFindings], 0);
      await github.rest.pulls.createReview({
        owner: context.repo.owner,
        repo: context.repo.repo,
        pull_number: pr.number,
        body: fallbackBody,
        event,
        comments: [],
      });
      core.info('Posted a comment-free Codex review after the inline comments were rejected.');
      return;
    }

    throw error;
  }
};
