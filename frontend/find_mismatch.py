import re

with open('/www/wwwroot/a22-com.site/frontend/src/views/master_admin/Users.vue', 'r') as f:
    balance = 0
    for i, line in enumerate(f, 1):
        if i > 942: break
        # Find all divs and template tags
        matches = re.findall(r'<(div|MasterAdminLayout)|</(div|MasterAdminLayout)', line)
        for open_tag, close_tag in matches:
            if open_tag:
                balance += 1
            if close_tag:
                balance -= 1
        if balance < 0:
            print(f"Mismatch at line {i}: balance {balance}")
            # balance = 0
    print(f"Final balance at line 942: {balance}")
