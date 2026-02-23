#!/usr/bin/env node
/**
 * Copy template images and site images to public so landing page images load.
 * Run after npm run build. Source: project root assets/ -> public/assets/
 */
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const root = path.resolve(__dirname, '..')
const publicDir = path.join(root, 'public', 'assets')
const sources = [
  { from: path.join(root, 'assets', 'templates'), to: path.join(publicDir, 'templates') },
  { from: path.join(root, 'assets', 'images'), to: path.join(publicDir, 'images') }
]

function copyRecursive (src, dest) {
  if (!fs.existsSync(src)) return
  fs.mkdirSync(dest, { recursive: true })
  for (const name of fs.readdirSync(src)) {
    const srcPath = path.join(src, name)
    const destPath = path.join(dest, name)
    if (fs.statSync(srcPath).isDirectory()) {
      copyRecursive(srcPath, destPath)
    } else {
      fs.copyFileSync(srcPath, destPath)
    }
  }
}

for (const { from, to } of sources) {
  if (fs.existsSync(from)) {
    copyRecursive(from, to)
    console.log('Copied:', from, '->', to)
  } else {
    console.warn('Skip (not found):', from)
  }
}

// Write version.txt in project root so you can check https://yoursite.com/version.txt
// If this file shows the latest date, the live site is serving from this server.
const versionPath = path.join(root, 'version.txt')
const versionContent = `LIVE_BUILD=${new Date().toISOString().slice(0, 19).replace('T', ' ')}
SERVER=${root}
Open this URL in browser: https://adsskillindia.in/version.txt
If you see this date, the site is from this server. Do hard refresh (Ctrl+Shift+R) on /user/courses to see changes.
`
fs.writeFileSync(versionPath, versionContent, 'utf8')
console.log('Updated:', versionPath)
