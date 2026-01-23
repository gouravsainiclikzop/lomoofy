# Integrations Module - Quick Start Guide

## 🚀 Accessing Integrations

1. Log in to Admin Panel
2. Navigate to **Settings → Integrations**
3. You'll see 6 integration cards

## 📝 Setting Up Each Integration

### Email (SMTP)
**Purpose:** Configure email delivery for order confirmations, notifications, etc.

**What You Need:**
- SMTP server details from your email provider
- Email account credentials
- Sender information

**Common Providers:**
- **Gmail**: smtp.gmail.com, Port 587, TLS
- **SendGrid**: smtp.sendgrid.net, Port 587, TLS
- **Mailgun**: smtp.mailgun.org, Port 587, TLS

**Steps:**
1. Enter SMTP Host (e.g., smtp.gmail.com)
2. Enter Port (usually 587 for TLS)
3. Select Encryption (TLS recommended)
4. Enter your email username
5. Enter your email password or app password
6. Set "From" email and name
7. Toggle Status to "Enabled"
8. Click "Save Configuration"

---

### Razorpay Payment
**Purpose:** Accept online payments from customers

**What You Need:**
- Razorpay account (test or live)
- API Key ID and Key Secret from Razorpay Dashboard

**Steps:**
1. Log in to Razorpay Dashboard
2. Go to Settings → API Keys
3. Generate or copy your Key ID
4. Copy your Key Secret
5. Copy Webhook Secret (from Webhooks section)
6. In the integration form:
   - Paste Key ID
   - Paste Key Secret
   - Paste Webhook Secret
   - Select Currency (INR default)
   - Choose Mode (Test for testing, Live for production)
   - Enable Status
7. Click "Save Configuration"

---

### OTP Service
**Purpose:** Send SMS OTP for customer verification

**What You Need:**
- Account with SMS provider (MSG91, Twilio, etc.)
- API credentials
- DLT-approved template ID (for India)

**Steps:**
1. Sign up with SMS provider
2. Get your API Key and Secret
3. Register your Sender ID (6 characters)
4. Create and approve OTP template
5. In the integration form:
   - Enter Provider Name
   - Paste API Key
   - Paste API Secret
   - Enter Sender ID
   - Enter Template ID
   - Set OTP Length (default: 6)
   - Set Expiry Time in seconds (default: 300)
   - Enable Status
6. Click "Save Configuration"

---

### WhatsApp Messaging
**Purpose:** Send order updates and notifications via WhatsApp

**What You Need:**
- WhatsApp Business API access (Meta/Twilio)
- Business phone number
- Access tokens

**Steps:**
1. Set up WhatsApp Business API
2. Get Phone Number ID from dashboard
3. Generate Access Token
4. Create Webhook Verify Token
5. In the integration form:
   - Enter Provider Name (e.g., Meta, Twilio)
   - Enter Business Phone Number
   - Paste Phone Number ID
   - Paste Access Token
   - Paste Webhook Verify Token
   - Set Default Country Code (e.g., +91)
   - Enable Status
6. Click "Save Configuration"

---

### Shopify
**Purpose:** Sync products and orders with Shopify store

**What You Need:**
- Shopify store
- Admin API access token
- API version

**Steps:**
1. Log in to Shopify Admin
2. Go to Apps → Develop apps
3. Create a new app
4. Generate Admin API access token
5. Note your API version (e.g., 2024-01)
6. In the integration form:
   - Enter Store URL (https://yourstore.myshopify.com)
   - Paste Admin API Access Token
   - Enter API Version
   - (Optional) Enter Webhook Secret
   - Check "Sync Products" if needed
   - Check "Sync Orders" if needed
   - Enable Status
7. Click "Save Configuration"

---

### Google Analytics
**Purpose:** Track website traffic and e-commerce data

**What You Need:**
- Google Analytics account
- GA4 Measurement ID or Universal Analytics ID
- (Optional) Measurement Protocol API Secret

**Steps:**
1. Log in to Google Analytics
2. Go to Admin → Property → Data Streams
3. Copy your Measurement ID (G-XXXXXXXXXX)
4. (Optional) Get Measurement Protocol API Secret
5. In the integration form:
   - Select Tracking Type (GA4 recommended)
   - Paste Measurement ID
   - (Optional) Paste API Secret
   - (Optional) Enter Property ID
   - Check "Enable Ecommerce Tracking" if needed
   - Enable Status
6. Click "Save Configuration"

---

## 🔐 Security Tips

1. **Password Fields**: All sensitive fields are automatically masked with `••••••••` after saving
2. **View Passwords**: Click the eye icon to temporarily view masked values
3. **Test First**: Use test/sandbox credentials before going live
4. **Regular Updates**: Update credentials periodically for security
5. **Access Control**: Only give admin access to trusted users

## ✅ Status Management

- **Green Badge = Enabled**: Integration is active and ready to use
- **Red Badge = Disabled**: Integration is saved but not active
- **Toggle Switch**: Click to enable/disable anytime

## 💡 Pro Tips

1. **Start with Test Mode**: For payment gateways, always test first
2. **Check Email Limits**: Most SMTP providers have daily sending limits
3. **DLT Registration**: For Indian SMS, DLT registration is mandatory
4. **Webhook URLs**: Note down webhook URLs when setting up services
5. **API Rate Limits**: Be aware of API call limits for each service

## 🐛 Troubleshooting

### Integration Won't Save
- Check all required fields (marked with *)
- Ensure you're logged in as admin
- Check browser console for errors

### Sensitive Fields Empty
- If you see `••••••••`, it means the field is masked
- Original value is preserved in database
- To change, just type new value

### Status Not Updating
- Refresh the page after saving
- Check if save was successful (success message)
- Verify in database if needed

## 📊 What Happens After Configuration?

**Nothing immediately** - This is a configuration-only module.

To actually use these integrations:
1. Development team will implement service logic
2. Services will read configurations from database
3. Features will be activated one by one
4. You'll be notified when each integration goes live

## 🎯 Common Use Cases

### Setting Up Email for Order Notifications
1. Configure Email (SMTP) integration
2. Enable status
3. Development team activates email sending
4. Customers receive order confirmations

### Accepting Online Payments
1. Configure Razorpay integration in Test mode
2. Test checkout flow
3. Switch to Live mode when ready
4. Start accepting real payments

### SMS OTP Verification
1. Configure OTP Service integration
2. Enable status
3. Development team activates OTP feature
4. Customers verify phone numbers

## 📞 Need Help?

1. Review this guide
2. Check the detailed documentation (INTEGRATIONS_MODULE_DOCUMENTATION.md)
3. Contact development team for technical issues
4. Refer to service provider's documentation for API details

---

**Remember**: This module only stores configurations. Actual integration functionality will be implemented by the development team based on your requirements.

**Date:** January 23, 2026
**Version:** 1.0.0
