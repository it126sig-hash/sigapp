import sys, json
from graphify.extract import collect_files, extract
from pathlib import Path

incremental = json.loads(Path('graphify-out/.graphify_incremental.json').read_text())
code_files = incremental.get('new_files', {}).get('code', [])

all_code_files = []
for f in code_files:
    p = Path(f)
    if p.is_dir():
        all_code_files.extend(collect_files(p))
    elif p.exists():
        all_code_files.append(p)

print('AST extraction: ' + str(len(all_code_files)) + ' code files...')
if all_code_files:
    result = extract(all_code_files)
    Path('graphify-out/.graphify_ast.json').write_text(json.dumps(result, indent=2))
    print('AST: ' + str(len(result['nodes'])) + ' nodes, ' + str(len(result['edges'])) + ' edges')
else:
    Path('graphify-out/.graphify_ast.json').write_text(json.dumps({'nodes':[],'edges':[],'input_tokens':0,'output_tokens':0}))
    print('No code files - skipping AST extraction')
