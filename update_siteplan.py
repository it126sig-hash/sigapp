import sys

file = 'app/Controllers/Siteplan.php'
with open(file, 'r', encoding='utf-8') as f:
    content = f.read()

s1 = """        if ($this->request->getPost('id_jenis') == "kavling") {
            $id_jalan = $this->request->getPost('id_jalan');"""
r1 = """        if (in_array($this->request->getPost('id_jenis'), ["kavling", "ruko"])) {
            $id_jalan = $this->request->getPost('id_jalan');"""

if s1 in content:
    content = content.replace(s1, r1)
else:
    print('s1 not found')

with open(file, 'w', encoding='utf-8') as f:
    f.write(content)
print('Siteplan.php updated')
