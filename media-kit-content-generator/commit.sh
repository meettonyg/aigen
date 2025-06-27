#!/bin/bash

# Add all files
git add .

# Commit with a detailed message
git commit -m "Fix Questions Generator Topic Question Sets

Implemented topic-based question set switching:
- Added showQuestionsForTopic method to show/hide question sets
- Enhanced selectTopic method to update both heading and question fields
- Simplified code to work as standalone generator without dependencies
- Improved user experience by showing relevant questions for each topic

This fixes the issue where changing topics didn't update the displayed
question fields (questions 1-5 for Topic 1, 6-10 for Topic 2, etc.)."

# Show commit details
git show --stat
