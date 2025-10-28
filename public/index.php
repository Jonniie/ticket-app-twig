<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Initialize Twig
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, ['debug' => false]);

// Add global variables
$twig->addGlobal('isAuthenticated', isset($_SESSION['user']));
$twig->addGlobal('user', $_SESSION['user'] ?? null);
$twig->addGlobal('toasts', $_SESSION['toasts'] ?? []);
unset($_SESSION['toasts']);

// Helper functions
function setFlash($type, $message) {
    if (!isset($_SESSION['toasts'])) {
        $_SESSION['toasts'] = [];
    }
    $_SESSION['toasts'][] = [
        'id' => time(),
        'type' => $type,
        'message' => $message
    ];
}

function redirect($path) {
    header("Location: $path");
    exit;
}

function getRoute() {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    // For development server, just return the path
    return $path ?: '/';
}

// Database class for file-based storage
class Database {
    private $usersFile;
    private $ticketsFile;

    public function __construct() {
        $dataDir = __DIR__ . '/../storage';
        @mkdir($dataDir, 0755, true);
        $this->usersFile = $dataDir . '/users.json';
        $this->ticketsFile = $dataDir . '/tickets.json';
        
        if (!file_exists($this->usersFile)) {
            file_put_contents($this->usersFile, json_encode([]));
        }
        if (!file_exists($this->ticketsFile)) {
            file_put_contents($this->ticketsFile, json_encode([]));
        }
    }

    public function getUsers() {
        $content = file_get_contents($this->usersFile);
        return json_decode($content, true) ?: [];
    }

    public function saveUsers($users) {
        file_put_contents($this->usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function getTickets() {
        $content = file_get_contents($this->ticketsFile);
        return json_decode($content, true) ?: [];
    }

    public function saveTickets($tickets) {
        file_put_contents($this->ticketsFile, json_encode($tickets, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function findUserByEmail($email) {
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }

    public function addUser($name, $email) {
        $users = $this->getUsers();
        
        // Check if user already exists
        if ($this->findUserByEmail($email)) {
            return ['success' => false, 'error' => 'User with this email already exists'];
        }
        
        $user = [
            'id' => time(),
            'name' => trim($name),
            'email' => trim($email),
            'role' => 'user'
        ];
        $users[] = $user;
        $this->saveUsers($users);
        return ['success' => true, 'user' => $user];
    }

    public function addTicket($userId, $title, $description = '', $priority = 'medium', $status = 'open') {
        $tickets = $this->getTickets();
        $ticket = [
            'id' => time(),
            'userId' => $userId,
            'title' => trim($title),
            'description' => trim($description),
            'priority' => $priority,
            'status' => $status,
            'createdAt' => date('c'),
            'updatedAt' => date('c')
        ];
        $tickets[] = $ticket;
        $this->saveTickets($tickets);
        return $ticket;
    }

    public function getUserTickets($userId) {
        $tickets = $this->getTickets();
        $userTickets = [];
        foreach ($tickets as $ticket) {
            if ($ticket['userId'] === $userId) {
                $userTickets[] = $ticket;
            }
        }
        return $userTickets;
    }

    public function getTicketById($ticketId) {
        $tickets = $this->getTickets();
        foreach ($tickets as $ticket) {
            if ($ticket['id'] === $ticketId) {
                return $ticket;
            }
        }
        return null;
    }

    public function updateTicket($ticketId, $data) {
        $tickets = $this->getTickets();
        $found = false;
        foreach ($tickets as &$ticket) {
            if ($ticket['id'] === $ticketId) {
                $ticket = array_merge($ticket, $data);
                $ticket['updatedAt'] = date('c');
                $found = true;
                break;
            }
        }
        if ($found) {
            $this->saveTickets($tickets);
            return true;
        }
        return false;
    }

    public function deleteTicket($ticketId) {
        $tickets = $this->getTickets();
        $newTickets = [];
        foreach ($tickets as $ticket) {
            if ($ticket['id'] !== $ticketId) {
                $newTickets[] = $ticket;
            }
        }
        $this->saveTickets($newTickets);
    }

    public function getTicketStats($userId) {
        $tickets = $this->getUserTickets($userId);
        return [
            'total' => count($tickets),
            'open' => count(array_filter($tickets, fn($t) => $t['status'] === 'open')),
            'inProgress' => count(array_filter($tickets, fn($t) => $t['status'] === 'in_progress')),
            'closed' => count(array_filter($tickets, fn($t) => $t['status'] === 'closed'))
        ];
    }
}

$db = new Database();
$route = getRoute();

// Handle API endpoints for AJAX requests
if ($route === '/api/tickets/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    
    $ticketId = isset($_POST['id']) ? (int)$_POST['id'] : null;
    if ($ticketId) {
        $ticket = $db->getTicketById($ticketId);
        if ($ticket && $ticket['userId'] === $_SESSION['user']['id']) {
            $db->deleteTicket($ticketId);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden']);
        }
    }
    exit;
}

// Handle form POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($route === '/auth/login') {
        $email = $_POST['email'] ?? '';
        
        // Validate email
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('error', 'Please enter a valid email address');
            redirect('/auth/login');
        }
        
        if ($user = $db->findUserByEmail($email)) {
            $_SESSION['user'] = $user;
            setFlash('success', 'Login successful! Welcome back.');
            redirect('/dashboard');
        } else {
            setFlash('error', 'Invalid credentials. Please check your email.');
            redirect('/auth/login');
        }
    } 
    elseif ($route === '/auth/signup') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';

        // Validate inputs
        if (!$name || !trim($name)) {
            setFlash('error', 'Name is required');
            redirect('/auth/signup');
        }

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('error', 'Please enter a valid email address');
            redirect('/auth/signup');
        }

        $result = $db->addUser($name, $email);
        if ($result['success']) {
            $_SESSION['user'] = $result['user'];
            setFlash('success', 'Sign up successful! Welcome to TicketApp.');
            redirect('/dashboard');
        } else {
            setFlash('error', $result['error']);
            redirect('/auth/signup');
        }
    } 
    elseif ($route === '/tickets/create' && isset($_SESSION['user'])) {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $priority = $_POST['priority'] ?? 'medium';

        // Validate
        if (!$title || !trim($title)) {
            setFlash('error', 'Title is required');
            redirect('/tickets/new');
        }

        if (strlen($description) > 1000) {
            setFlash('error', 'Description must be less than 1000 characters');
            redirect('/tickets/new');
        }

        $db->addTicket($_SESSION['user']['id'], $title, $description, $priority, 'open');
        setFlash('success', 'Ticket created successfully!');
        redirect('/tickets');
    }
    elseif (preg_match('#^/tickets/(\d+)/update$#', $route, $matches) && isset($_SESSION['user'])) {
        $ticketId = (int)$matches[1];
        $ticket = $db->getTicketById($ticketId);

        if (!$ticket || $ticket['userId'] !== $_SESSION['user']['id']) {
            setFlash('error', 'Ticket not found');
            redirect('/tickets');
        }

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 'open';
        $priority = $_POST['priority'] ?? 'medium';

        // Validate
        if (!$title || !trim($title)) {
            setFlash('error', 'Title is required');
            redirect("/tickets/{$ticketId}/edit");
        }

        if (!in_array($status, ['open', 'in_progress', 'closed'])) {
            setFlash('error', 'Invalid status');
            redirect("/tickets/{$ticketId}/edit");
        }

        if (strlen($description) > 1000) {
            setFlash('error', 'Description must be less than 1000 characters');
            redirect("/tickets/{$ticketId}/edit");
        }

        $db->updateTicket($ticketId, [
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'priority' => $priority
        ]);
        setFlash('success', 'Ticket updated successfully!');
        redirect('/tickets');
    }
}

// Handle GET requests
switch ($route) {
    case '/':
        echo $twig->render('landing.html.twig');
        break;

    case '/auth/login':
        if (isset($_SESSION['user'])) redirect('/dashboard');
        echo $twig->render('auth/login.html.twig');
        break;

    case '/auth/signup':
        if (isset($_SESSION['user'])) redirect('/dashboard');
        echo $twig->render('auth/signup.html.twig');
        break;

    case '/dashboard':
        if (!isset($_SESSION['user'])) redirect('/auth/login');
        $tickets = $db->getUserTickets($_SESSION['user']['id']);
        // Sort by updatedAt descending and take last 3
        usort($tickets, fn($a, $b) => strtotime($b['updatedAt']) - strtotime($a['updatedAt']));
        $recentTickets = array_slice($tickets, 0, 3);
        $stats = $db->getTicketStats($_SESSION['user']['id']);
        echo $twig->render('dashboard.html.twig', ['stats' => $stats, 'recentTickets' => $recentTickets]);
        break;

    case '/tickets':
        if (!isset($_SESSION['user'])) redirect('/auth/login');
        $tickets = $db->getUserTickets($_SESSION['user']['id']);
        // Sort by createdAt descending
        usort($tickets, fn($a, $b) => strtotime($b['createdAt']) - strtotime($a['createdAt']));
        echo $twig->render('tickets/list.html.twig', ['tickets' => $tickets]);
        break;

    case '/tickets/new':
        if (!isset($_SESSION['user'])) redirect('/auth/login');
        echo $twig->render('tickets/form.html.twig', ['isEdit' => false]);
        break;

    case '/auth/logout':
        session_destroy();
        setFlash('success', 'Logged out successfully!');
        redirect('/');
        break;

    default:
        // Check for /tickets/{id}/edit pattern
        if (preg_match('#^/tickets/(\d+)/edit$#', $route, $matches)) {
            if (!isset($_SESSION['user'])) redirect('/auth/login');
            $ticketId = (int)$matches[1];
            $ticket = $db->getTicketById($ticketId);
            if (!$ticket || $ticket['userId'] !== $_SESSION['user']['id']) {
                setFlash('error', 'Ticket not found');
                redirect('/tickets');
            }
            echo $twig->render('tickets/form.html.twig', ['isEdit' => true, 'ticket' => $ticket]);
        } else {
            http_response_code(404);
            echo $twig->render('404.html.twig');
        }
}
