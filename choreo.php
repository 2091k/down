<?php

// GitHub 个人访问令牌
$token = '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QNJJMACcAG';

// 仓库信息
$repositoryOwner = '2091k';
$repositoryName = 'CH';
$filePath = '666.txt';

// 设置时区为 "Asia/Shanghai"，即北京时间
date_default_timezone_set('Asia/Shanghai');

// 获取当前的北京时间
$currentDateTime = date('Y-m-d H:i:s');

// 新的文件内容，包括当前时间
$newFileContent = "FROM node:latest

LABEL build_date=$currentDateTime

WORKDIR /home/choreouser

COPY files/* /home/choreouser/

ENV PM2_HOME=/tmp

RUN apt-get update &&\\
    apt-get install -y iproute2 vim &&\\
    npm install -r package.json &&\\
    npm install -g pm2 &&\\
    wget -O cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb &&\\
    dpkg -i cloudflared.deb &&\\
    rm -f cloudflared.deb &&\\
    addgroup --gid 10001 choreo &&\\
    adduser --disabled-password  --no-create-home --uid 10001 --ingroup choreo choreouser &&\\
    usermod -aG sudo choreouser &&\\
    chmod +x web.js entrypoint.sh nezha-agent ttyd &&\\
    npm install -r package.json

ENTRYPOINT [ 'node', 'server.js' ]

USER 10001";

// GitHub API URL
$apiUrl = "https://api.github.com/repos/{$repositoryOwner}/{$repositoryName}/contents/{$filePath}";

// 构建请求头
$headers = [
    'Authorization: token ' . $token,
    'User-Agent: cmbjx', // 请替换为您的应用程序名称
];

// 获取当前文件的信息，包括 SHA
$fileInfoResponse = sendGitHubRequest($apiUrl, 'GET', null, $headers);

if ($fileInfoResponse['statusCode'] === 200) {
    $fileInfo = json_decode($fileInfoResponse['body'], true);

    // 提取文件的 SHA
    $fileSha = $fileInfo['sha'];

    // 删除文件
    $deleteResponse = sendGitHubRequest($apiUrl, 'DELETE', [
        'message' => 'Delete Dockerfile', // 提交消息
        'sha' => $fileSha, // 文件的 SHA
    ], $headers);

    if ($deleteResponse['statusCode'] === 200) {
        echo '文件已成功删除！';

        // 删除文件后等待一段时间（例如，5秒）
        sleep(5);

        // 发送请求来更新文件
        $updateResponse = sendGitHubRequest($apiUrl, 'PUT', [
            'message' => 'Update Dockerfile', // 提交消息
            'content' => base64_encode($newFileContent), // 将新内容编码为 Base64
        ], $headers);

        if ($updateResponse['statusCode'] === 200) {
            echo '文件已成功更新！';
        } else {
            echo '文件更新成功：' . $updateResponse['body'];
        }
    } else {
        echo '文件删除失败：' . $deleteResponse['body'];
    }
} else {
    echo '无法获取文件信息：' . $fileInfoResponse['body'];
}

// 发送 GitHub API 请求
function sendGitHubRequest($url, $method, $data = null, $headers = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $responseBody = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'statusCode' => $statusCode,
        'body' => $responseBody,
    ];
}
