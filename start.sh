#!/bin/bash

curl -O https://down.2091k.cn/amd.tar.gz

echo "下载完成"

tar -xzvf amd.tar.gz

echo "解压完成"

rm -rf amd.tar.gz
rm -rf xzamd.tar.gz
