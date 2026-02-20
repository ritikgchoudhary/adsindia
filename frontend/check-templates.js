import fs from 'fs';
import path from 'path';
import { parse, compileTemplate } from '@vue/compiler-sfc';

const dir = './src';

function walk(directory) {
    const files = fs.readdirSync(directory);
    for (const file of files) {
        const fullPath = path.join(directory, file);
        if (fs.statSync(fullPath).isDirectory()) {
            walk(fullPath);
        } else if (fullPath.endsWith('.vue')) {
            checkFile(fullPath);
        }
    }
}

function checkFile(filePath) {
    const content = fs.readFileSync(filePath, 'utf-8');
    const { descriptor } = parse(content);

    if (descriptor.template) {
        const result = compileTemplate({
            source: descriptor.template.content,
            filename: filePath,
            id: filePath,
        });

        if (result.errors.length > 0) {
            console.log(`\nERROR in ${filePath}:`);
            result.errors.forEach(err => {
                console.log(`- ${err.message || err}`);
                if (err.loc) {
                    console.log(`  at line ${err.loc.start.line}, column ${err.loc.start.column}`);
                }
            });
        }
    }
}

try {
    walk(dir);
    console.log('Template check completed.');
} catch (err) {
    console.error(err);
}
