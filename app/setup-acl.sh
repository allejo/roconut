#!/bin/bash

rm -rf var/*

HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`

if [[ "$OSTYPE" == "darwin"* ]]; then
    sudo chmod +a "$HTTPDUSER allow delete,write,append,file_inherit,directory_inherit" var
    sudo chmod +a "$(whoami) allow delete,write,append,file_inherit,directory_inherit" var
else
    if hash setfacl 2>/dev/null; then
        sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var
        sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var
    else
        echo "You're missing ACL on your system. Install the \`acl\` package respectively with apt-get or yum."
    fi
fi
