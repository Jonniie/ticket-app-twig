# TicketApp - Twig Implementation

A modern, intuitive ticket management system built with PHP, Twig templating engine, and Tailwind CSS. This is the Twig version of the TicketApp, providing the same functionality as the React version with server-side rendering.

## 🚀 Features

- **User Authentication**
  - Sign up with name and email
  - Login with email
  - Session-based authentication
  - Logout functionality

- **Ticket Management**
  - Create new tickets with title, description, and priority
  - Edit existing tickets (update title, description, status, and priority)
  - Delete tickets with confirmation
  - Track ticket status: Open, In Progress, Closed

- **Dashboard**
  - Overview of ticket statistics
  - Display recent ticket activity (last 3 tickets)
  - Quick action links to manage and create tickets
  - Welcome message with user's name

- **Tickets List**
  - View all tickets in grid or list view
  - Search tickets by title or description
  - Filter tickets by status
  - Toggle between grid and list views
  - Delete tickets with action menu

- **Responsive Design**
  - Mobile-first responsive design
  - Beautiful UI with Tailwind CSS
  - Smooth animations and transitions

## 📁 Project Structure

```
ticket-app-twig/
├── public/
│   └── index.php              # Main entry point
├── src/
│   ├── controllers/           # (Optional) For future controller expansion
│   └── models/                # (Optional) For future model expansion
├── storage/
│   ├── users.json            # User data storage
│   └── tickets.json          # Ticket data storage
├── templates/
│   ├── base.html.twig        # Base layout template
│   ├── landing.html.twig     # Landing/home page
│   ├── dashboard.html.twig   # Dashboard page
│   ├── 404.html.twig         # 404 error page
│   ├── auth/
│   │   ├── login.html.twig   # Login form
│   │   └── signup.html.twig  # Signup form
│   ├── layout/
│   │   ├── header.html.twig  # Header component
│   │   └── footer.html.twig  # Footer component
│   └── tickets/
│       ├── list.html.twig    # Tickets list page
│       └── form.html.twig    # Ticket create/edit form
├── composer.json             # PHP dependencies
└── README.md                 # This file
```

## 🛠️ Installation

### Requirements
- PHP >= 8.0
- Composer

### Setup Steps

1. **Navigate to the project directory:**
```bash
cd ticket-app-twig
```

2. **Install dependencies:**
```bash
composer install
```

3. **Start the PHP server:**
```bash
php -S localhost:8000 -t public
```

4. **Access the application:**
Open your browser and navigate to `http://localhost:8000`

## 📝 Usage

### Public Routes

| Route | Method | Description |
|-------|--------|-------------|
| `/` | GET | Landing page with features |
| `/auth/login` | GET | Login form |
| `/auth/login` | POST | Login submission |
| `/auth/signup` | GET | Signup form |
| `/auth/signup` | POST | Signup submission |

### Protected Routes

| Route | Method | Description |
|-------|--------|-------------|
| `/dashboard` | GET | Dashboard with stats and recent tickets |
| `/tickets` | GET | List all tickets with search and filtering |
| `/tickets/new` | GET | Create new ticket form |
| `/tickets/create` | POST | Create ticket submission |
| `/tickets/{id}/edit` | GET | Edit ticket form |
| `/tickets/{id}/update` | POST | Update ticket submission |
| `/auth/logout` | GET | Logout |

### API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/tickets/delete` | POST | Delete a ticket (AJAX) |

## 💾 Data Storage

The application uses file-based JSON storage for data persistence:

- **users.json**: Stores user account information
- **tickets.json**: Stores all tickets with their metadata

Data is stored in the `/storage` directory.

## 🔐 Security Features

- Session-based authentication
- Email validation
- Protected routes (redirect to login if not authenticated)
- Ownership verification (users can only see their own tickets)
- CSRF-like protection through form submission

## 🎨 UI/UX Features

- **Modern Design**: Clean, minimal interface with Tailwind CSS
- **Responsive Layout**: Works seamlessly on desktop, tablet, and mobile
- **Status Badges**: Visual indicators for ticket status (Open, In Progress, Closed)
- **Priority Colors**: Color-coded priority levels (High=Red, Medium=Yellow, Low=Green)
- **Toast Notifications**: Flash messages for user feedback
- **Loading States**: Visual feedback for form submissions

## 🚀 Key Pages

### Landing Page
- Hero section with feature highlights
- Call-to-action buttons
- Feature showcase with icons
- Professional testimonial-style section

### Authentication
- Clean login form with email field
- Comprehensive signup form with email validation
- Error messages with visual indicators

### Dashboard
- Stats cards showing total, open, in progress, and closed tickets
- Recent activity section showing last 3 tickets
- Quick action buttons for common tasks

### Tickets List
- Powerful search functionality
- Status-based filtering
- Grid and list view toggle
- Action menu for edit/delete
- Delete confirmation dialog

### Ticket Form
- Title (required)
- Description (optional, max 1000 chars)
- Status selection (disabled for new tickets, defaults to Open)
- Priority selection (Low, Medium, High)
- Client-side form validation
- Character counter for description

## 📋 Ticket Object Structure

```json
{
  "id": 1234567890,
  "userId": 1234567890,
  "title": "Bug in login",
  "description": "Users cannot login with email",
  "priority": "high",
  "status": "open",
  "createdAt": "2024-01-15T10:30:00+00:00",
  "updatedAt": "2024-01-15T10:30:00+00:00"
}
```

## 👤 User Object Structure

```json
{
  "id": 1234567890,
  "name": "John Doe",
  "email": "john@example.com",
  "role": "user"
}
```

## 🔄 Workflow

1. **New User**: Signs up → Creates account → Logged in → Redirected to dashboard
2. **Existing User**: Logs in → Session created → Access dashboard and tickets
3. **Create Ticket**: Click "New Ticket" → Fill form → Submit → Stored in JSON → Redirected to list
4. **Edit Ticket**: Click edit menu → Open form → Update fields → Submit → Updated in JSON
5. **Delete Ticket**: Click delete menu → Confirm → Removed from JSON
6. **Logout**: Click logout → Session destroyed → Redirected to home

## 📱 Responsive Breakpoints

- Mobile: Default (< 640px)
- Small: `sm:` (≥ 640px)
- Medium: `md:` (≥ 768px)
- Large: `lg:` (≥ 1024px)

## 🎯 Form Validation

### Client-side
- Required field checks
- Email format validation
- Description length validation (max 1000 chars)
- Status validation

### Server-side
- Email format validation with `filter_var()`
- Title trimming and validation
- Description length check
- Status enum validation
- Ticket ownership verification

## 🔧 Configuration

Currently, the application uses default configuration. To customize:

1. **Change storage directory**: Edit `$dataDir` in `public/index.php`
2. **Modify Tailwind styling**: Edit utility classes in templates
3. **Adjust flash message timing**: Modify CSS animation in `base.html.twig`

## 📄 License

MIT License - Feel free to use this project for learning and development.
