#!/bin/bash

# Record archive organization changes

git add .
git commit -m "Organized file structure: moved archived files, backups, test files to appropriate sub-folders"
git push

echo "Archive organization complete and committed to git."
