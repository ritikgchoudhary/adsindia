#!/bin/bash

# Auto Commit Script
# This script automatically commits and pushes changes to GitHub

cd "$(dirname "$0")"

# Check if there are any changes
if [ -z "$(git status --porcelain)" ]; then
    echo "No changes to commit."
    exit 0
fi

# Get current branch
BRANCH=$(git rev-parse --abbrev-ref HEAD)

# Add all changes
echo "Adding changes..."
git add -A

# Create commit message with timestamp
COMMIT_MSG="Auto commit: $(date '+%Y-%m-%d %H:%M:%S')"

# Commit changes
echo "Committing changes..."
git commit -m "$COMMIT_MSG"

# Push to GitHub
echo "Pushing to GitHub..."
git push origin $BRANCH

if [ $? -eq 0 ]; then
    echo "✓ Successfully committed and pushed to GitHub!"
    echo "  Branch: $BRANCH"
    echo "  Commit: $COMMIT_MSG"
else
    echo "✗ Failed to push to GitHub. Please push manually."
    exit 1
fi
