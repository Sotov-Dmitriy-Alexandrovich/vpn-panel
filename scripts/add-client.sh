#!/bin/bash
CLIENT="$1"
[ -z "$CLIENT" ] && { echo "ERROR:NAME_EMPTY"; exit 1; }

OVPN_DIR="/etc/openvpn/server"
EASYRSA="$OVPN_DIR/easy-rsa"
[ ! -d "$EASYRSA" ] && { echo "ERROR:EASYRSA_NOT_FOUND"; exit 1; }

cd "$EASYRSA" || { echo "ERROR:CD_FAILED"; exit 1; }

# Генерируем сертификат в batch-режиме (без вопросов)
./easyrsa --batch build-client-full "$CLIENT" nopass >/dev/null 2>&1
[ $? -ne 0 ] && { echo "ERROR:CERT_GEN_FAILED"; exit 1; }

# Создаём папку для скачивания
mkdir -p /opt/vpn-panel/www/downloads

# Собираем .ovpn файл
{
  cat "$OVPN_DIR/client-common.txt"
  echo "<ca>"
  cat "$OVPN_DIR/ca.crt"
  echo "</ca>"
  echo "<cert>"
  cat "$EASYRSA/pki/issued/$CLIENT.crt"
  echo "</cert>"
  echo "<key>"
  cat "$EASYRSA/pki/private/$CLIENT.key"
  echo "</key>"
  echo "<tls-crypt>"
  cat "$OVPN_DIR/tc.key"
  echo "</tls-crypt>"
} > "/opt/vpn-panel/www/downloads/$CLIENT.ovpn"

[ -f "/opt/vpn-panel/www/downloads/$CLIENT.ovpn" ] && echo "SUCCESS:$CLIENT" || { echo "ERROR:FILE_NOT_CREATED"; exit 1; }
