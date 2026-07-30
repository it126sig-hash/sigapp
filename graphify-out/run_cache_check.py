import json
from graphify.cache import check_semantic_cache
from pathlib import Path

incremental = json.loads(Path('graphify-out/.graphify_incremental.json').read_text())
new_files = incremental.get('new_files', {})

# Get all non-code files
all_files = []
for cat in ['document', 'paper', 'image']:
    all_files.extend(new_files.get(cat, []))

print('Total non-code files to check: ' + str(len(all_files)))

cached_nodes, cached_edges, cached_hyperedges, uncached = check_semantic_cache(all_files)

if cached_nodes or cached_edges or cached_hyperedges:
    Path('graphify-out/.graphify_cached.json').write_text(json.dumps({
        'nodes': cached_nodes,
        'edges': cached_edges,
        'hyperedges': cached_hyperedges
    }))
else:
    Path('graphify-out/.graphify_cached.json').write_text(json.dumps({
        'nodes': [], 'edges': [], 'hyperedges': []
    }))

Path('graphify-out/.graphify_uncached.txt').write_text('\n'.join(uncached))
cached_count = len(all_files) - len(uncached)
print('Cache: ' + str(cached_count) + ' files hit, ' + str(len(uncached)) + ' files need extraction')
print('Cached nodes: ' + str(len(cached_nodes)) + ', edges: ' + str(len(cached_edges)))
