"""
Semantic extraction for doc/paper/image files (sequential - OpenClaw platform).
Processes files from .graphify_uncached.txt and writes results to .graphify_semantic_new.json.
"""
import json
from pathlib import Path

uncached_text = Path('graphify-out/.graphify_uncached.txt').read_text().strip()
if not uncached_text:
    all_files = []
else:
    all_files = [f for f in uncached_text.split('\n') if f.strip()]

# Filter: skip pure vendor/flag SVGs and minified JS/CSS (not meaningful for app graph)
SKIP_PATTERNS = [
    'app-assets/fonts/flag-icon-css',
    'app-assets/fonts/feather',
    'app-assets/vendors',
    'app-assets/js',
    'app-assets/css',
    'cleave-phone.',  # hundreds of country phone format JS files
]

def should_skip(filepath):
    fp = filepath.replace('\\', '/')
    for pat in SKIP_PATTERNS:
        if pat in fp:
            return True
    return False

filtered = [f for f in all_files if not should_skip(f)]
skipped = len(all_files) - len(filtered)
print('Files to extract: ' + str(len(filtered)) + ' (skipped ' + str(skipped) + ' vendor/asset files)')

# Categorize
docs = [f for f in filtered if Path(f).suffix.lower() in {'.md', '.html', '.txt', '.rst', '.yaml', '.yml'}]
papers = [f for f in filtered if Path(f).suffix.lower() == '.pdf']
images = [f for f in filtered if Path(f).suffix.lower() in {'.png', '.jpg', '.jpeg', '.gif', '.webp', '.svg'}]

print('docs: ' + str(len(docs)) + ', papers: ' + str(len(papers)) + ', images: ' + str(len(images)))

# Write the filtered list back so extraction step knows what to process
Path('graphify-out/.graphify_filtered.txt').write_text('\n'.join(filtered))
print('Filtered list written to graphify-out/.graphify_filtered.txt')
