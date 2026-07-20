#!/bin/bash

set -e

CLIENT="$1"

[ -z "$CLIENT" ] && {
    echo "ERROR:NAME_EMPTY"
    exit 1
}

OVPN=/etc/openvpn/server
EASY=$OVPN/easy-rsa
DOWNLOAD=/opt/vpn-panel/www/downloads

cd "$EASY"

CRT=$(find pki/issued -maxdepth 1 -iname "$CLIENT.crt" | head -n1)

if [ -z "$CRT" ]; then
    echo "ERROR:CLIENT_NOT_FOUND"
    exit 1
fi

CLIENT=$(basename "$CRT" .crt)

echo "Removing $CLIENT"

printf "yes\n" | ./easyrsa revoke "$CLIENT"

./easyrsa gen-crl

cp pki/crl.pem "$OVPN/crl.pem"

chmod 644 "$OVPN/crl.pem"

chown nobody:nogroup "$OVPN/crl.pem" 2>/dev/null || true

rm -f pki/issued/$CLIENT.crt
rm -f pki/private/$CLIENT.key
rm -f pki/reqs/$CLIENT.req
rm -f pki/inline/$CLIENT.inline
rm -f pki/inline/private/$CLIENT.inline

sed -i "\|/CN=$CLIENT\$|d" pki/index.txt

rm -f "$DOWNLOAD/$CLIENT.ovpn"

find /root -type f -iname "$CLIENT.ovpn" -delete

systemctl restart openvpn-server@server.service 2>/dev/null || \
systemctl restart openvpn 2>/dev/null || true

echo "SUCCESS:$CLIENT"