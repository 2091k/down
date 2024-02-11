const { spawn } = require('child_process');
const fs = require('fs');

const startScriptPath = './start.sh';
const interpreterPath = '/usr/bin/env';
const interpreterArgs = ['bash', startScriptPath];

try {
  fs.chmodSync(startScriptPath, 0o755);
  console.log(`璧嬫潈鎴愬姛: ${startScriptPath}`);
} catch (error) {
  console.error(`璧嬫潈澶辫触: ${error}`);
}

const startScript = spawn(interpreterPath, interpreterArgs);

startScript.stdout.on('data', (data) => {
  console.log(`杈撳嚭锛�${data}`);
});

startScript.stderr.on('data', (data) => {
  console.error(`${data}`);
});

startScript.on('error', (error) => {
  console.error(`鍚姩鑴氭湰閿欒: ${error}`);
  process.exit(1); 
});

startScript.on('close', (code) => {
  console.log(`瀛愯繘绋嬮€€鍑猴紝閫€鍑虹爜 ${code}`);
});
