export ZSH=$HOME/.oh-my-zsh

ZSH_THEME="evan"

# Aliases
alias grep="grep --color"

DISABLE_AUTO_UPDATE="true"
DISABLE_CORRECTION="true"

plugins=(git git-extras composer)

source $ZSH/oh-my-zsh.sh

export PATH=/usr/local/bin:/usr/local/sbin:$PATH
