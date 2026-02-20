#!/usr/bin/env node
/**
 * Watch frontend src/ and run full build on change.
 * So you can edit files and see changes on live (after refresh) without running "npm run build" manually.
 * Usage: node watch-and-build.js   OR   npm run build:watch
 */
import { watch } from 'fs'
import { spawn } from 'child_process'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const srcDir = path.join(__dirname, 'src')
let building = false
let pending = false

function runBuild() {
  if (building) {
    pending = true
    return
  }
  building = true
  const child = spawn('npm', ['run', 'build'], {
    cwd: __dirname,
    stdio: 'inherit',
    shell: true
  })
  child.on('close', (code) => {
    building = false
    if (code === 0) console.log('[watch] Build done. Refresh https://adsskillindia.in/ to see changes.')
    if (pending) {
      pending = false
      runBuild()
    }
  })
}

console.log('[watch] Watching src/ – save any file to auto-build. Refresh site after build.')
runBuild()

watch(srcDir, { recursive: true }, (event, filename) => {
  if (!filename || building) return
  if (/\.(vue|js|ts|css|scss|html)$/i.test(filename)) {
    console.log('[watch] Change:', filename)
    runBuild()
  }
})
