<?php

$config = <<<EOF
##############################################
# Created by ZeroCz's nginx config generator #
# https://github.com/Ondra3211               #
##############################################

server {
	listen 80;
	listen [::]:80;

	server_name [DOMAIN];
    include /etc/nginx/ssl/headers.conf;
	return 301 https://[DOMAIN]\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
	server_name [DOMAIN];
	root [ROOT];
	index [INDEX];

    include /etc/nginx/ssl/ssl.conf;
    include /etc/nginx/ssl/headers.conf;
    ssl_certificate /root/.acme.sh/[SSL]/fullchain.cer;
    ssl_certificate_key /root/.acme.sh/[SSL]/[SSL].key;
    ssl_trusted_certificate /root/.acme.sh/[SSL]/ca.cer;

	location / {
		try_files \$uri \$uri/ =404;
    }
    [PHP]
}
EOF;

$a_domain = '';

while (!$a_domain)
{
    $a_domain = trim(readline('Název domény: '));
}

$a_php = '';

while ($a_php != 'a' && $a_php != 'n')
{
    $a_php = strtolower(readline('Povolit PHP (A/N): '));
}

$a_root = '';

while(!$a_root)
{
    $a_root = trim(readline('Cesta k hlavní složce webu: '));
}

$domain = $a_domain;
$root = $a_root;
$index = 'index.html';
$ssl = getDomain($a_domain);
$php = '';


if ($a_php == 'a') {
    $index = 'index.php ' . $index;
    $php = <<<EOF

        location ~ \.php$ {
            include fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        }
    EOF;
}

$config = str_replace(['[DOMAIN]', '[ROOT]', '[INDEX]', '[SSL]', '[PHP]'], [$domain, $root, $index, $ssl, $php], $config);

file_put_contents('/etc/nginx/sites-available/' . $domain . '.conf', $config);

echo 'Konfigurace vytvořena: /etc/nginx/sites-available/' . $domain . '.conf' . PHP_EOL;

function getDomain($domain)
{
    $result = explode('.', $domain, 2);
    return isset($result[1]) ? $result[1] : $result[0];
}


