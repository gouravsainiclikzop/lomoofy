# Change Password Page - Visual Walkthrough

## Page Layout: `/change-password`

---

## View 1: Default - Change Password Form

```
╔══════════════════════════════════════════════════════════════╗
║                    LOMOOFY CLOTHING                          ║
╠══════════════════════════════════════════════════════════════╣
║  Home > Dashboard > Change Password                          ║
╠══════════════════════════════════════════════════════════════╣
║                                                              ║
║  [Dashboard Sidebar]  ┃  ┌─────────────────────────────┐   ║
║  • Dashboard          ┃  │ Change Password [Forgot?]   │   ║
║  • Profile Info       ┃  ├─────────────────────────────┤   ║
║  • Change Password ✓  ┃  │                             │   ║
║  • Addresses          ┃  │  Old Password *              │   ║
║  • Orders             ┃  │  [________________]          │   ║
║  • Wishlist           ┃  │                             │   ║
║  • Logout             ┃  │  New Password *              │   ║
║                       ┃  │  [________________]          │   ║
║                       ┃  │                             │   ║
║                       ┃  │  Confirm Password *          │   ║
║                       ┃  │  [________________]          │   ║
║                       ┃  │                             │   ║
║                       ┃  │  [Change Password] [Cancel] │   ║
║                       ┃  │                             │   ║
║                       ┃  └─────────────────────────────┘   ║
║                       ┃                                    ║
╚══════════════════════════════════════════════════════════════╝
```

---

## View 2: After Clicking "Forgot Password?"

```
╔══════════════════════════════════════════════════════════════╗
║                    LOMOOFY CLOTHING                          ║
╠══════════════════════════════════════════════════════════════╣
║  Home > Dashboard > Change Password                          ║
╠══════════════════════════════════════════════════════════════╣
║                                                              ║
║  [Dashboard Sidebar]  ┃  ┌─────────────────────────────┐   ║
║  • Dashboard          ┃  │ Change Password             │   ║
║  • Profile Info       ┃  ├─────────────────────────────┤   ║
║  • Change Password ✓  ┃  │                             │   ║
║  • Addresses          ┃  │  ← Back to Change Password  │   ║
║  • Orders             ┃  │                             │   ║
║  • Wishlist           ┃  │  ┌──────────────────────┐   │   ║
║  • Logout             ┃  │  │ ℹ️  Enter your email │   │   ║
║                       ┃  │  │    to receive a      │   │   ║
║                       ┃  │  │    verification code │   │   ║
║                       ┃  │  └──────────────────────┘   │   ║
║                       ┃  │                             │   ║
║                       ┃  │  Email Address *             │   ║
║                       ┃  │  [____________________]      │   ║
║                       ┃  │                             │   ║
║                       ┃  │  [Send Verification Code]   │   ║
║                       ┃  │                             │   ║
║                       ┃  └─────────────────────────────┘   ║
║                       ┃                                    ║
╚══════════════════════════════════════════════════════════════╝
```

---

## View 3: OTP Verification Step

```
╔══════════════════════════════════════════════════════════════╗
║                    LOMOOFY CLOTHING                          ║
╠══════════════════════════════════════════════════════════════╣
║  Home > Dashboard > Change Password                          ║
╠══════════════════════════════════════════════════════════════╣
║                                                              ║
║  [Dashboard Sidebar]  ┃  ┌─────────────────────────────┐   ║
║  • Dashboard          ┃  │ Change Password             │   ║
║  • Profile Info       ┃  ├─────────────────────────────┤   ║
║  • Change Password ✓  ┃  │                             │   ║
║  • Addresses          ┃  │  ← Back to Change Password  │   ║
║  • Orders             ┃  │                             │   ║
║  • Wishlist           ┃  │  ┌──────────────────────┐   │   ║
║  • Logout             ┃  │  │ ✅ Code sent to your │   │   ║
║                       ┃  │  │    email!            │   │   ║
║                       ┃  │  └──────────────────────┘   │   ║
║                       ┃  │                             │   ║
║                       ┃  │  ┌──────────────────────┐   │   ║
║                       ┃  │  │ 📧 We've sent a code │   │   ║
║                       ┃  │  │    to user@email.com │   │   ║
║                       ┃  │  └──────────────────────┘   │   ║
║                       ┃  │                             │   ║
║                       ┃  │  Verification Code *         │   ║
║                       ┃  │  [______]  (6 digits)       │   ║
║                       ┃  │                             │   ║
║                       ┃  │  Didn't receive?            │   ║
║                       ┃  │  Resend Code (45s)          │   ║
║                       ┃  │                             │   ║
║                       ┃  │  [Verify Code] [Change Email]│  ║
║                       ┃  │                             │   ║
║                       ┃  └─────────────────────────────┘   ║
║                       ┃                                    ║
╚══════════════════════════════════════════════════════════════╝
```

---

## View 4: Reset Password Step

```
╔══════════════════════════════════════════════════════════════╗
║                    LOMOOFY CLOTHING                          ║
╠══════════════════════════════════════════════════════════════╣
║  Home > Dashboard > Change Password                          ║
╠══════════════════════════════════════════════════════════════╣
║                                                              ║
║  [Dashboard Sidebar]  ┃  ┌─────────────────────────────┐   ║
║  • Dashboard          ┃  │ Change Password             │   ║
║  • Profile Info       ┃  ├─────────────────────────────┤   ║
║  • Change Password ✓  ┃  │                             │   ║
║  • Addresses          ┃  │  ← Back to Change Password  │   ║
║  • Orders             ┃  │                             │   ║
║  • Wishlist           ┃  │  ┌──────────────────────┐   │   ║
║  • Logout             ┃  │  │ ✅ Email verified!   │   │   ║
║                       ┃  │  │    Set your new      │   │   ║
║                       ┃  │  │    password          │   │   ║
║                       ┃  │  └──────────────────────┘   │   ║
║                       ┃  │                             │   ║
║                       ┃  │  New Password *              │   ║
║                       ┃  │  [________________] [👁]    │   ║
║                       ┃  │  Minimum 8 characters        │   ║
║                       ┃  │                             │   ║
║                       ┃  │  Confirm Password *          │   ║
║                       ┃  │  [________________] [👁]    │   ║
║                       ┃  │                             │   ║
║                       ┃  │  [Reset Password]           │   ║
║                       ┃  │                             │   ║
║                       ┃  └─────────────────────────────┘   ║
║                       ┃                                    ║
╚══════════════════════════════════════════════════════════════╝
```

---

## View 5: Success & Auto-Return

```
╔══════════════════════════════════════════════════════════════╗
║                    LOMOOFY CLOTHING                          ║
╠══════════════════════════════════════════════════════════════╣
║  Home > Dashboard > Change Password                          ║
╠══════════════════════════════════════════════════════════════╣
║                                                              ║
║  [Dashboard Sidebar]  ┃  ┌─────────────────────────────┐   ║
║  • Dashboard          ┃  │ Change Password             │   ║
║  • Profile Info       ┃  ├─────────────────────────────┤   ║
║  • Change Password ✓  ┃  │                             │   ║
║  • Addresses          ┃  │  ← Back to Change Password  │   ║
║  • Orders             ┃  │                             │   ║
║  • Wishlist           ┃  │  ┌──────────────────────┐   │   ║
║  • Logout             ┃  │  │ ✅ Password reset    │   │   ║
║                       ┃  │  │    successfully! You │   │   ║
║                       ┃  │  │    can now login with│   │   ║
║                       ┃  │  │    your new password │   │   ║
║                       ┃  │  │                   [×]│   │   ║
║                       ┃  │  └──────────────────────┘   │   ║
║                       ┃  │                             │   ║
║                       ┃  │  Returning to change         │   ║
║                       ┃  │  password form...            │   ║
║                       ┃  │                             │   ║
║                       ┃  │  (Auto-returns in 3s)       │   ║
║                       ┃  │                             │   ║
║                       ┃  └─────────────────────────────┘   ║
║                       ┃                                    ║
╚══════════════════════════════════════════════════════════════╝
```

---

## Mobile View

### Portrait Mode (375px)

```
┌────────────────────────────┐
│  ☰  LOMOOFY CLOTHING  🛒  │
├────────────────────────────┤
│  ← Back  Change Password   │
├────────────────────────────┤
│                            │
│  ┌────────────────────┐   │
│  │ Change Password    │   │
│  │            [Forgot?]│  │
│  ├────────────────────┤   │
│  │                    │   │
│  │ Old Password *     │   │
│  │ [______________]   │   │
│  │                    │   │
│  │ New Password *     │   │
│  │ [______________]   │   │
│  │                    │   │
│  │ Confirm Password * │   │
│  │ [______________]   │   │
│  │                    │   │
│  │ [Change Password]  │   │
│  │ [Cancel]           │   │
│  │                    │   │
│  └────────────────────┘   │
│                            │
└────────────────────────────┘
```

---

## Loading States

### Sending OTP

```
┌─────────────────────────────────┐
│  Email Address *                │
│  user@example.com               │
│                                 │
│  [⏳ Sending...]                │
│   └─ Spinner animation          │
│                                 │
└─────────────────────────────────┘
```

### Verifying OTP

```
┌─────────────────────────────────┐
│  Verification Code *            │
│  123456                         │
│                                 │
│  [⏳ Verifying...]              │
│   └─ Spinner animation          │
│                                 │
│  Didn't receive?                │
│  Resend Code (disabled)         │
│                                 │
└─────────────────────────────────┘
```

### Resetting Password

```
┌─────────────────────────────────┐
│  New Password *                 │
│  ••••••••••                     │
│                                 │
│  Confirm Password *             │
│  ••••••••••                     │
│                                 │
│  [⏳ Resetting...]              │
│   └─ Spinner animation          │
│                                 │
└─────────────────────────────────┘
```

---

## Error States

### Invalid Email

```
┌─────────────────────────────────┐
│  ❌ Email not found in system   │
│     or email not registered     │
└─────────────────────────────────┘
│                                 │
│  Email Address *                │
│  user@example.com               │
│  └─ ❌ Please check your email  │
│                                 │
└─────────────────────────────────┘
```

### Invalid OTP

```
┌─────────────────────────────────┐
│  ❌ Invalid or expired code     │
└─────────────────────────────────┘
│                                 │
│  Verification Code *            │
│  123456                         │
│  └─ ❌ Please enter valid code  │
│                                 │
│  Didn't receive?                │
│  Resend Code                    │
│                                 │
└─────────────────────────────────┘
```

### Password Mismatch

```
┌─────────────────────────────────┐
│  New Password *                 │
│  ••••••••••                     │
│                                 │
│  Confirm Password *             │
│  ••••••••••                     │
│  └─ ❌ Passwords do not match   │
│                                 │
└─────────────────────────────────┘
```

---

## Interactive Elements

### Password Toggle

```
Before Click:
[password123••••] [👁️]
                   ↑
                 Click

After Click:
[password123____] [👁️‍🗨️]
    ↑                ↑
 Visible        Eye slashed
```

### Resend Timer

```
Initial:
Resend Code (60s)  ← Gray, disabled

After 30s:
Resend Code (30s)  ← Still gray

After 60s:
Resend Code        ← Blue, clickable ✅
```

### Back Button

```
┌─────────────────────────┐
│ ← Back to Change Password│  ← Always visible
└─────────────────────────┘
```

---

## Color Scheme

```
┌─────────────────────────────────────┐
│  Alert Types                        │
├─────────────────────────────────────┤
│  ✅ Success  → Green (#28a745)     │
│  ❌ Error    → Red (#dc3545)       │
│  ℹ️  Info     → Blue (#17a2b8)     │
│  ⚠️  Warning  → Yellow (#ffc107)   │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  Buttons                            │
├─────────────────────────────────────┤
│  Primary   → Dark (#212529)         │
│  Secondary → Gray (#6c757d)         │
│  Disabled  → Light Gray (#e9ecef)   │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  Links                              │
├─────────────────────────────────────┤
│  Default   → Blue (#007bff)         │
│  Hover     → Dark Blue (#0056b3)    │
│  Disabled  → Gray (#6c757d)         │
└─────────────────────────────────────┘
```

---

## Animation Flow

```
View Transitions:
┌──────────────┐
│ Change Pwd   │
└──────┬───────┘
       │ Fade Out (200ms)
       ▼
┌──────────────┐
│ Forgot Pwd   │
└──────┬───────┘
       │ Fade In (200ms)
       │
       │ User completes flow
       │
       ▼
┌──────────────┐
│ Success Msg  │
└──────┬───────┘
       │ Display 3s
       │
       ▼
┌──────────────┐
│ Change Pwd   │  (Returns)
└──────────────┘
```

---

## Responsive Breakpoints

```
┌─────────────────────────────────────┐
│  Breakpoint      Layout              │
├─────────────────────────────────────┤
│  > 992px        Sidebar + Content    │
│  768px-991px    Collapsed Sidebar    │
│  < 768px        Stacked (mobile)     │
└─────────────────────────────────────┘
```

---

## Accessibility Features

### Screen Reader Text
```html
<label for="fpEmail">
    Email Address <span class="text-danger">*</span>
    <span class="sr-only">Required field</span>
</label>
```

### Keyboard Navigation
- Tab through all fields ✅
- Enter to submit forms ✅
- Esc to close alerts ✅
- Focus indicators visible ✅

### ARIA Labels
```html
<button aria-label="Show password">
    <i class="fas fa-eye"></i>
</button>
```

---

## Print Styles

When printing the page:
```
┌─────────────────────────────────┐
│  LOMOOFY CLOTHING               │
│  Change Password                │
├─────────────────────────────────┤
│                                 │
│  [Form contents displayed]      │
│                                 │
│  (Sidebar hidden)               │
│  (Buttons simplified)           │
│  (Colors adjusted for B&W)      │
│                                 │
└─────────────────────────────────┘
```

---

**Visual Guide Complete** ✅

This visual walkthrough demonstrates all states and transitions of the forgot password functionality integrated into the change password page.

