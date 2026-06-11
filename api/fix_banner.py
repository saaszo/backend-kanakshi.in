import os
import glob
import re

base_path = '/Users/pankaj/Desktop/e-commerce/e commerce project /backend/api/resources/views/admin'
files = glob.glob(f"{base_path}/**/*.blade.php", recursive=True)

for file in files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
        
    original = content
    
    # We find admin-banner, then its inner contents.
    # If the first tag inside isn't a <div>, we wrap it.
    
    def replacer(match):
        inner = match.group(1)
        # If the inner content already starts with <div>, <form> or has it wrapped, leave it alone.
        # But wait, it could start with <div class="brand">. In that case, we STILL need a wrapper div!
        # Because we want a container for the left side text.
        # Let's check if the first tag is exactly <div> without attributes.
        stripped = inner.strip()
        if stripped.startswith('<div>') or stripped.startswith('<div class="d-flex'):
            return f'<div class="admin-banner">{inner}</div>'
        
        # Otherwise, we wrap everything that isn't a .toolbar-actions or <a> button at the end into a <div>.
        # But this is complex. Let's just wrap the WHOLE thing in <div> if it doesn't have a toolbar-actions.
        if 'toolbar-actions' not in inner and '<a href=' not in inner:
            return f'<div class="admin-banner">\n                    <div>{inner}</div>\n                </div>'
        else:
            # manually wrap the first part? It's better to just manually fix the 2-3 files that are broken.
            return f'<div class="admin-banner">{inner}</div>'

    # Actually let's just print which files don't have <div> directly after admin-banner
    
    for m in re.finditer(r'<div class="admin-banner[^"]*">\s*(<[^>]+>)', content):
        tag = m.group(1)
        if not tag.startswith('<div>'):
            print(f"Suspicious banner in {file}: {tag}")
            
