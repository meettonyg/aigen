#!/bin/bash

# Add all files
git add .

# Commit with a detailed message
git commit -m "Update Questions Generator Field Labels

Changed question labels to use ordinal naming:
- Renamed 'Question 1, 2, 3' to 'First, Second, Third Interview Question'
- Updated the section heading to 'Interview Questions for \"Topic Name\"'
- Added explanatory subheading 'Each topic has 5 interview questions'
- Updated placeholders in text fields to match the new naming scheme
- Fixed heading updates when selecting different topics

This provides better clarity about the question hierarchy
and improves the user experience when filling out the form."

# Show commit details
git show --stat
