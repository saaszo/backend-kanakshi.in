import os
import glob
import re

base_path = '/Users/pankaj/Desktop/e-commerce/e commerce project /backend/api/resources/views/admin'
files = glob.glob(f"{base_path}/**/*.blade.php", recursive=True)

for file in files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
        
    original = content
    
    # Fix the broken toast structure:
    # <div class="admin-toast">
    # <div>
    #     <strong>Success!</strong>
    #     <p>{{ session('status') }}</div>
    
    pattern = re.compile(r'<div class="admin-toast">\s*<div>\s*<strong>Success!</strong>\s*<p>(.*?)</div>', re.DOTALL)
    
    # Replace with proper structure
    content = pattern.sub(r'<div class="admin-toast">\n    <div>\n        <strong>Success!</strong>\n        <p>\1</p>\n    </div>\n</div>', content)
    
    if content != original:
        with open(file, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Fixed {file}")
