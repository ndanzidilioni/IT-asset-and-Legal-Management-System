# 🔐 Login Guide for ICT Asset Register

## Quick Setup & Login

### 1. Setup the System
```bash
cd Backend
php setup_with_login.php
```

### 2. Start the Servers
```bash
# Terminal 1 - Backend
cd Backend
php artisan serve

# Terminal 2 - Frontend  
cd frontend
npm start
```

### 3. Access the Application
- Open your browser and go to: **http://localhost:3000**
- You'll be redirected to the login page

## 🔑 Default Login Credentials

| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| **Admin** | `admin@system.com` | `admin123` | Full system access |
| **Developer** | `developer@system.com` | `dev123` | Development tools |
| **Client** | `client@system.com` | `client123` | Client features |

## 🎯 What You Can Do After Login

### For All Users:
- ✅ View and manage ICT assets
- ✅ Access the comprehensive dashboard
- ✅ Use advanced filtering and search
- ✅ Import/export asset data
- ✅ Generate reports

### Admin Features:
- ✅ Full system administration
- ✅ User management
- ✅ System configuration
- ✅ All asset management features

### Developer Features:
- ✅ Asset registration and management
- ✅ Technical specifications tracking
- ✅ Maintenance scheduling
- ✅ Network asset monitoring

### Client Features:
- ✅ View assigned assets
- ✅ Submit asset requests
- ✅ Access basic reporting

## 🚀 After Login - Available Pages

1. **Dashboard** - Overview and analytics
2. **IT Assets** - Asset management and registration
3. **Inquiry Report** - System inquiries
4. **Developer Panel** - Development tools
5. **Client Panel** - Client-specific features

## 🔧 Troubleshooting

### Can't Login?
1. Make sure both servers are running
2. Check if the database is properly set up
3. Verify the setup script ran successfully
4. Try refreshing the page

### Forgot Password?
- Use the default credentials above
- Or create a new user via the register page

### Database Issues?
```bash
cd Backend
php artisan migrate:fresh --seed
```

## 📱 Mobile Access
The system is responsive and works on mobile devices. Access via:
- **http://localhost:3000** (when running locally)
- Or your server's IP address if deployed

## 🔒 Security Note
**Important**: Change the default passwords in production environments!

---

**Ready to start?** Run the setup script and login with any of the credentials above! 🎉
