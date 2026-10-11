╔══════════════════════════════════════════════════════════════════════╗
║           CHN101 STUDENT AIDE — SETUP & INSTALLATION GUIDE          ║
╚══════════════════════════════════════════════════════════════════════╝

A full-stack PHP + MySQL website for learning Mandarin Chinese.
Built for XAMPP · phpMyAdmin · Notepad++ on Windows.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
STEP 1 — INSTALL XAMPP
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. Download XAMPP from https://www.apachefriends.org
2. Install it (default path: C:\xampp)
3. Open the XAMPP Control Panel
4. Click START next to Apache
5. Click START next to MySQL
6. Both should show green "Running" status

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
STEP 2 — COPY PROJECT FILES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. Copy the entire "chn101" folder into:
   C:\xampp\htdocs\

   Final path should be:  C:\xampp\htdocs\chn101\

2. Verify you can see:
   C:\xampp\htdocs\chn101\index.php
   C:\xampp\htdocs\chn101\database.sql
   C:\xampp\htdocs\chn101\css\style.css
   etc.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
STEP 3 — CREATE THE DATABASE (phpMyAdmin)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. Open your browser and go to:
   http://localhost/phpmyadmin

2. Click "New" in the left sidebar to create a database

3. Name it exactly:  chn101_db
   Collation:         utf8mb4_unicode_ci
   Click "Create"

4. With chn101_db selected, click the "Import" tab at the top

5. Click "Choose File" → navigate to:
   C:\xampp\htdocs\chn101\database.sql

6. Scroll down, click "Import" (or "Go")

7. You should see "Import has been successfully finished"
   The left panel will now show all tables:
   ✅ admins
   ✅ flashcards
   ✅ lessons
   ✅ personal_flashcards
   ✅ quiz_answers
   ✅ quiz_attempts
   ✅ quiz_questions
   ✅ student_progress
   ✅ students

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
STEP 4 — CONFIGURE DATABASE CONNECTION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Open:  C:\xampp\htdocs\chn101\includes\db.php

The default settings match XAMPP defaults:
   DB_HOST  = localhost
   DB_USER  = root
   DB_PASS  = (empty — no password)
   DB_NAME  = chn101_db

If you've set a MySQL root password, update DB_PASS accordingly.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
STEP 5 — OPEN IN BROWSER
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Student site:    http://localhost/chn101/
Admin login:     http://localhost/chn101/admin_login.php
phpMyAdmin:      http://localhost/phpmyadmin

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
DEFAULT LOGIN CREDENTIALS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
ADMIN:
  Username: admin
  Password: password

SAMPLE STUDENTS (all use password: password):
  RahimBD     (Bangla language preference)
  KarimCN     (English language preference)
  NahalEdu    (English language preference)
  AishaBD     (Bangla language preference)
  TanvirLearn (English language preference)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
STEP 6 — VIEW & EDIT CODE IN NOTEPAD++
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. Download Notepad++ from https://notepad-plus-plus.org
2. Open Notepad++
3. File → Open Folder as Workspace
   → Browse to C:\xampp\htdocs\chn101
   → Click "Select Folder"
4. The entire project tree appears in the left sidebar
5. Click any file to open it with syntax highlighting

Recommended Notepad++ Plugins (Plugin Admin):
  • Explorer       — file tree browser
  • Compare        — diff two files side by side
  • XML Tools      — format/validate HTML
  • JSMin          — minify JavaScript
  • PHP Syntax Check — validate PHP before saving

Useful keyboard shortcuts in Notepad++:
  Ctrl+F         — Find in current file
  Ctrl+Shift+F   — Find in all files (search project)
  Ctrl+G         — Go to line number
  Alt+Shift+Enter — Full screen
  F5             — Run / launch in browser (configure Run menu)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PROJECT FILE STRUCTURE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
chn101/
├── index.php                ← Main homepage
├── login.php                ← Student login
├── register.php             ← Student registration
├── logout.php               ← Logout handler
├── profile.php              ← Student profile & settings
├── admin_login.php          ← Admin login portal
├── database.sql             ← Full database schema + sample data
├── 404.php                  ← Error page
│
├── css/
│   └── style.css            ← All styles (theme, components, responsive)
│
├── js/
│   └── main.js              ← Shared JavaScript (quiz logic, modals, etc.)
│
├── includes/
│   ├── db.php               ← PDO database connection + session helpers
│   ├── header.php           ← Shared nav header
│   └── footer.php           ← Shared footer
│
├── ajax/
│   ├── set_lang.php         ← Save preferred language via AJAX
│   └── get_questions.php    ← Fetch quiz questions as JSON
│
├── admin/
│   ├── index.php            ← Admin dashboard (stats + student overview)
│   ├── students.php         ← All students list
│   ├── student_detail.php   ← Individual student progress + quiz history
│   ├── flashcards.php       ← Manage all official flashcards
│   ├── add_flashcard.php    ← Add new official flashcard (with live preview)
│   ├── edit_flashcard.php   ← Edit existing flashcard
│   ├── questions.php        ← Manage all quiz questions
│   ├── add_question.php     ← Add new quiz question (bilingual)
│   ├── edit_question.php    ← Edit existing quiz question
│   ├── lessons.php          ← Manage lessons (add/delete)
│   ├── reports.php          ← Analytics: charts, lesson stats, top students
│   └── logout.php           ← Admin logout
│
├── flashcards/
│   ├── index.php            ← Browse lessons (choose lesson to study)
│   ├── lesson.php           ← Study a lesson: flip cards, browse/study mode, add personal cards
│   └── my_cards.php         ← View all personal flashcards across all lessons
│
├── quiz/
│   ├── index.php            ← Quiz home (lesson picker with progress)
│   ├── take.php             ← Interactive quiz (randomised, bilingual)
│   ├── submit.php           ← AJAX: saves score, updates streak & points
│   ├── history.php          ← Student's full quiz attempt history
│   └── result.php           ← Detailed result + per-question breakdown
│
└── leaderboard/
    ├── index.php            ← Global leaderboard (top 10 by points + podium)
    └── streaks.php          ← Streak leaderboard (current + longest ever tabs)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
FEATURES SUMMARY
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Feature 1 — Admin Dashboard
  ✅ View all students with streak, quizzes, average score
  ✅ Click into any student for detailed quiz history
  ✅ Add new quiz questions (English + Bangla, 4 options)
  ✅ Edit / delete existing quiz questions
  ✅ Manage official flashcards (add/edit/delete with live preview)
  ✅ Manage lessons (add/delete)
  ✅ Reports page: per-lesson stats, bar chart, language distribution

Feature 2 — Personalised Flashcards per Lesson
  ✅ Browse all lessons and select one to study
  ✅ Official flashcards with Chinese, Pinyin, and meaning
  ✅ English / Bangla language toggle (saved per student)
  ✅ Browse mode (grid) AND Study mode (single card + navigation + shuffle)
  ✅ Create personal flashcards for any lesson (stored per student)
  ✅ View all personal cards across lessons on My Cards page
  ✅ Delete personal cards

Feature 3 — Interactive Quiz with Scoring & Daily Streak
  ✅ Quiz for any lesson with randomised questions
  ✅ Questions and options in English or Bangla
  ✅ Instant colour-coded feedback after each answer
  ✅ Score saved to database on completion
  ✅ Daily streak auto-updated (consecutive days)
  ✅ Points system: 10 pts/correct + 20 bonus for perfect score
  ✅ Full quiz history with progress bars
  ✅ Detailed result page with per-question breakdown

Feature 4 — Leaderboard
  ✅ Top 10 by total points with gold/silver/bronze podium
  ✅ Shows username, streak, points, lessons completed
  ✅ Your rank bar always visible at top
  ✅ Streak sub-leaderboard (current streak + longest ever tabs)
  ✅ Auto-updates after every quiz submission

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
TROUBLESHOOTING
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
• Blank page?
  → Open includes/db.php and check DB_PASS (try '' or 'root')
  → Make sure Apache & MySQL are both running in XAMPP

• "Access denied" error?
  → Open phpMyAdmin → User accounts → root → Edit privileges
  → Ensure root@localhost has full privileges

• "Table doesn't exist"?
  → Re-import database.sql via phpMyAdmin Import tab

• Port conflict (Apache won't start)?
  → XAMPP Control Panel → Apache → Config → httpd.conf
  → Change "Listen 80" to "Listen 8080"
  → Access site at http://localhost:8080/chn101/

• Session/login issues?
  → Check that session.auto_start=0 in php.ini
  → Clear browser cookies

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Built with: PHP 8+, MySQL 8, HTML5, CSS3, JavaScript (vanilla)
No external dependencies needed beyond XAMPP.
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
