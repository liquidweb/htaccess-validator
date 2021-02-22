#!/usr/bin/env bash

if ! command -v shellcheck &> /dev/null; then
    echo -e "\033[0;33mShellcheck is not currently installed\033[0;0m"
    echo 'See https://www.shellcheck.net/ for installation instructions.'
    exit 2
fi

shellcheck bin/validate-htaccess && echo 'No issues detected!'
