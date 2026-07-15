#!/bin/bash
CLIENT="$1"
[ -z "$CLIENT" ] && { echo "ERROR:NAME_EMPTY"; exit 1; }

OVPN_DIR="/etc/openvpn/server"
EASYRSA="$OVPN_DIR/easy-rsa"
[ ! -d "$EASYRSA" ] && { echo "ERROR:EASYRSA_NOT_FOUND"; exit 1; }

cd "$EASYRSA" || { echo "ERROR:CD_FAILED"; exit 1; }

# 1. Отзываем сертификат
./easyrsa --batch revoke "$CLIENT" >/dev/null 2>&1
[ $? -ne 0 ] && { echo "ERROR:REVOKE_FAILED"; exit 1; }

# 2. Генерируем новый CRL
./easyrsa --batch gen-crl >/dev/null 2>&1
[ $? -ne 0 ] && { echo "ERROR:CRL_GEN_FAILED"; exit 1; }

# 3. Копируем crl.pem в папку OpenVPN (с правильными правами)
cp "$EASYRSA/pki/crl.pem" "$OVPN_DIR/crl.pem"
chown nobody:nogroup "$OVPN_DIR/crl.pem"
chmod 644 "$OVPN_DIR/crl.pem"

# 4. Перезагружаем OpenVPN (чтобы подхватил новый CRL)
systemctl reload openvpn-server@server.service 2>/dev/null || systemctl restart openvpn 2>/dev/null || true

# 5. Удаляем .ovpn файл из папки скачивания
rm -f "/opt/vpn-panel/www/downloads/$CLIENT.ovpn"

echo "SUCCESS:REVOKED:$CLIENT"
