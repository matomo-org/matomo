const fs = require('fs');

function requiredEnv(name) {
  const value = process.env[name];
  if (value === undefined || value === '') {
    throw new Error(`Missing required environment variable: ${name}`);
  }
  return value;
}

function optionalEnv(name) {
  return process.env[name] || '';
}

function replaceAll(input, replacements) {
  let output = input;
  for (const [key, value] of Object.entries(replacements)) {
    output = output.split(`{{${key}}}`).join(value);
  }
  return output;
}

const promptTemplate = requiredEnv('PROMPT_TEMPLATE');
const promptOutput = requiredEnv('PROMPT_OUTPUT');
const reviewContext = requiredEnv('REVIEW_CONTEXT');

const context = {
  pr_number: Number(requiredEnv('PR_NUMBER')),
  base_ref: requiredEnv('BASE_REF'),
  base_sha: requiredEnv('BASE_SHA'),
  head_ref: requiredEnv('HEAD_REF'),
  head_sha: requiredEnv('HEAD_SHA'),
  merge_ref: requiredEnv('MERGE_REF'),
  changed_files: JSON.parse(requiredEnv('CHANGED_FILES')),
};

fs.writeFileSync(reviewContext, `${JSON.stringify(context, null, 2)}\n`);

const template = fs.readFileSync(promptTemplate, 'utf8');
const prompt = replaceAll(template, {
  PR_NUMBER: String(context.pr_number),
  PR_TITLE: optionalEnv('PR_TITLE'),
  PR_BODY: optionalEnv('PR_BODY'),
  BASE_REF: context.base_ref,
  BASE_SHA: context.base_sha,
  HEAD_REF: context.head_ref,
  HEAD_SHA: context.head_sha,
  MERGE_REF: context.merge_ref,
  REVIEW_CONTEXT: reviewContext,
});

fs.writeFileSync(promptOutput, prompt);
