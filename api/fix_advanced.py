import os
import glob
import re

base_path = '/Users/pankaj/Desktop/e-commerce/e commerce project /backend/api/resources/views/admin'
files = glob.glob(f"{base_path}/**/*.blade.php", recursive=True)

for file in files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
        
    original = content
    
    # regex sub page-head
    content = re.sub(r'class="page-head\b.*?"', 'class="admin-banner"', content)
    content = re.sub(r"class='page-head\b.*?'", 'class="admin-banner"', content)
    
    # regex sub panel
    # wait, if I just replace 'panel', what if it's already 'admin-section'?
    content = re.sub(r'class="panel(\s+.*?)*"', lambda m: 'class="admin-section' + m.group(1) + '"' if m.group(1) else 'class="admin-section"', content)
    
    if content != original:
        with open(file, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Fixed advanced {file}")
