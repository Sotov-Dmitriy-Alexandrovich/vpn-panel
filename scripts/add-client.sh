#!/bin/bash

set -e

CLIENT="$1"

[ -z "$CLIENT" ] && {
    echo "ERROR:NAME_EMPTY"
    exit 1
}

[[ "$CLIENT" =~ ^[A-Za-z0-9_-]+$ ]] || {
    echo "ERROR:INVALID_NAME"
    exit 1
}

OVPN=/etc/openvpn/server
EASY=$OVPN/easy-rsa
DOWNLOAD=/opt/vpn-panel/www/downloads

cd "$EASY"

if grep -q "/CN=$CLIENT\$" pki/index.txt; then
    echo "ERROR:CLIENT_EXISTS"
    exit 1
fi

./easyrsa --batch build-client-full "$CLIENT" nopass

mkdir -p "$DOWNLOAD"

cat "$OVPN/client-common.txt" \
<(echo "<ca>") \
"$OVPN/ca.crt" \
<(echo "</ca><cert>") \
"pki/issued/$CLIENT.crt" \
<(echo "</cert><key>") \
"pki/private/$CLIENT.key" \
<(echo "</key><tls-crypt>") \
"$OVPN/tc.key" \
<(echo "</tls-crypt>") \
> "$DOWNLOAD/$CLIENT.ovpn"

echo "SUCCESS:$CLIENT"