const fs = require('fs');

const SEVERITIES = ['none', 'low', 'medium', 'blocking'];

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

function validateReview(review) {
  if (!review || typeof review !== 'object' || Array.isArray(review)) {
    throw new Error('Codex output must be a JSON object');
  }

  assertString(review.review_body_markdown, 'review_body_markdown');
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
  }

  for (const [index, finding] of review.unplaced_findings.entries()) {
    if (!['low', 'medium', 'blocking'].includes(finding.severity)) {
      throw new Error(`unplaced_findings[${index}].severity is invalid`);
    }
    assertString(finding.body, `unplaced_findings[${index}].body`);
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

function appendUnplacedFindings(body, findings) {
  if (findings.length === 0) {
    return body;
  }

  return `${body.trim()}\n\nUnplaced Inline Findings\n${findings.map(formatFinding).join('\n')}\n`;
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

module.exports = async function postReview({ github, context, core }) {
  const pr = context.payload.pull_request;
  const safetyFailure = requiredEnv('PREFLIGHT_SAFETY_FAILURE') === 'true';
  const safetyMessage = process.env.PREFLIGHT_SAFETY_MESSAGE || '';
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
  const unplaced = [...review.unplaced_findings];

  for (const comment of review.inline_comments) {
    const patch = patchesByPath.get(comment.path);
    const valid = patch
      && (comment.side === 'RIGHT'
        ? patch.right.has(comment.line)
        : patch.left.has(comment.line));

    if (!valid) {
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
  }

  const body = appendUnplacedFindings(review.review_body_markdown, unplaced);
  const event = reviewEventForSeverity(review.highest_severity);

  try {
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
    throw error;
  }
};
