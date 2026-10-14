"""Rebuild the five-page Tahoma 12 report after personalising config/site.php.

Requires reportlab and Pillow. Run from any folder: python docs/build_report.py
Use --font-dir on another computer to point to licensed Tahoma font files.
"""
import argparse
from html import escape
from pathlib import Path
import re
from PIL import Image as PILImage
from reportlab.lib import colors
from reportlab.lib.enums import TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, PageBreak, Image, Table, TableStyle

parser = argparse.ArgumentParser()
parser.add_argument('--font-dir', default='C:/Windows/Fonts')
args = parser.parse_args()
root = Path(__file__).resolve().parents[1]
fontdir = Path(args.font_dir)
pdfmetrics.registerFont(TTFont('Tahoma', str(fontdir / 'tahoma.ttf')))
pdfmetrics.registerFont(TTFont('TahomaBold', str(fontdir / 'tahomabd.ttf')))
pdfmetrics.registerFontFamily('Tahoma', normal='Tahoma', bold='TahomaBold', italic='Tahoma', boldItalic='TahomaBold')
body = ParagraphStyle('Body', fontName='Tahoma', fontSize=12, leading=17, spaceAfter=12, textColor=colors.black)
heading = ParagraphStyle('Heading', parent=body, fontName='TahomaBold', spaceBefore=12, spaceAfter=10, keepWithNext=True)
caption = ParagraphStyle('Caption', parent=body, leading=16)
config = dict(re.findall(r"'([a-z_]+)'\s*=>\s*'([^']*)'", (root / 'config/site.php').read_text(encoding='utf-8')))
story = []
markdown = []

def h(text):
    story.append(Paragraph(escape(text), heading))
    markdown.extend(['## ' + text, ''])

def p(text):
    story.append(Paragraph(escape(text), body))
    markdown.extend([text, ''])

def page():
    story.append(PageBreak())
    markdown.extend(['---', ''])

def link(label, url):
    story.append(Paragraph(f'<a href="{url}" color="#234d38">{escape(label)}</a>', body))
    markdown.extend([f'[{label}]({url})', ''])

h('Salone Studio PHP Authentication System and Dashboard')
p('Web Programming Assignment 1 | Limkokwing University Sierra Leone')
p(f"Student: {config['student_name']} | ID: {config['student_id']} | Programme: {config['programme']} | Semester: {config['semester']} | Year: {config['year']}")
h('The idea behind Salone Studio')
p("Salone Studio is a small learning website for women interested in programming. It draws on the Django Girls community in Sierra Leone. Users can sign up, log in and follow four short web lessons. The dashboard shows the learner's own goal and saved progress.")
p('The official Django Girls Freetown page describes a workshop held in October 2017. That event gives the project a local context. Salone Studio is an independent coursework website. It does not advertise a new workshop or represent Django Girls officially.')
h('The look of the site')
p('The Django Girls logo sits at the top of every page. Cream backgrounds keep the pages light, while navy headings and orange buttons stand out. A small Sierra Leone flag, Freetown coordinates and a coast-and-hills illustration connect the design to its local setting.')
p('The reference image guided the warm colours and rounded shapes. White cards and soft shadows separate each part of the page. Segoe UI and Tahoma are available locally, with a small Georgia accent in the footer. The CSS was written for this project, without a purchased theme or plugin. This report uses Tahoma at 12 points.')
p('The illustration shows two women building a website together. It is an SVG file, so it stays sharp on different screens. Floating notes add depth around it. Cards rise slightly on hover, and the CSS turns off transitions for visitors who prefer reduced motion.')
page()

h('Home page screenshot')
p('Figure 1 shows the full home page served by XAMPP Apache. Larger desktop and phone screenshots are also saved in the project folder.')
imgpath = root / 'docs/screenshots/home-desktop.jpg'
with PILImage.open(imgpath) as image:
    iw, ih = image.size
image_height = 615
image_width = image_height * iw / ih
story.append(Image(str(imgpath), width=image_width, height=image_height, hAlign='CENTER'))
markdown.extend(['![Complete home page](screenshots/home-desktop.jpg)', ''])
page()

h('What a learner can do')
p('The home page introduces the studio before asking anyone to create an account. Its main button opens signup. The header and student footer stay in the same place across the site, so the pages feel connected.')
p('To sign up, a learner enters a name, email, goal and password, then confirms the password. If a field is wrong, the form explains what needs fixing. Password fields stay blank after an error. PHP checks the submission even if browser checks are bypassed, and it rejects an email already in use.')
p('Once the account is created, a message asks the learner to log in. The dashboard then greets them by name and shows their goal, progress and recent activity. New accounts start at zero. A card points to the first unfinished lesson.')
p('The four lessons cover HTML, CSS, PHP and account security. Each has a short explanation, sample code and a practice task. PHP marks a lesson complete after the learner answers its question correctly. Answering again does not add extra progress. The result is saved for the next visit; it is a learning record rather than a qualification.')
p('My profile lets the learner change their name and goal. The resource shelf links to official guides from Django Girls, MDN, PHP and GitHub. These open in a new tab, so the workspace stays open. Log out ends the session and takes the learner back to the login page.')
h('Using the site on different screens')
p('On a larger screen, the home page text sits beside the illustration. The dashboard has navigation on the left. On a phone, the content stacks in one column and the navigation becomes compact. Form fields have labels and visible keyboard focus. A finished lesson has a text label as well as a colour change. The skip link leads straight to the main content, and screen readers ignore decorative icons.')
p('The forms send data straight to PHP; they do not need JavaScript. Apache serves the pages, and MySQL stores accounts and progress. Both XAMPP services need to be running for the account features to work. Images and fonts are local files, so they do not need an internet connection.')
page()

h('How the PHP code works')
p("PHP builds the pages and handles form data. Shared functions cover sessions, database access, headers and footers. The lessons are held in an array. A loop displays them, while conditions decide which lesson comes next and which ones are finished. Forms and session variables carry the learner's details between requests.")
p("The site connects to XAMPP's MySQL server through PDO. Setup begins by importing database/schema.sql in phpMyAdmin. A unique email rule prevents duplicate accounts. Foreign keys link progress and activity to a valid user, so those records belong to the right account.")
h('Protecting passwords and data')
p('Registration hashes a password with password_hash and PASSWORD_DEFAULT. Login checks it with password_verify. PHP can also update an older hash when needed. The database never saves the original password. Passwords are limited to 72 bytes to avoid bcrypt ignoring part of a longer value.')
p('Database queries use prepared statements, which treat user input as data rather than SQL instructions. The e helper uses htmlspecialchars before a name or other account value appears on the page. Profile and progress requests take the user ID from the session. A submitted ID cannot make them act on another account.')
h('Sessions and forms')
p('After login, PHP changes the session ID. Cookies use HttpOnly and SameSite Lax, with Secure enabled over HTTPS. A session expires after 30 minutes without activity. Logout only accepts a POST request. PHP checks its CSRF token, then clears the session and expires the cookie.')
p('Forms use a CSRF token to link a submission to the session that created it. The login page limits repeated failures for both an account and an IP address. Its error message does not reveal whether an email exists. Security headers block scripts and embedding in other pages. Pages with account details tell the browser not to cache them.')
p("The project is installed in C:/xampp/htdocs/salone-studio. Apache's .htaccess rules block direct access to private folders. MySQL holds the account records; the storage folder holds PHP session files that are excluded from GitHub. Application pages require Apache. If MySQL stops, account requests return HTTP 503. Public hosting would also need HTTPS and restricted database credentials.")
page()

h('Checks and assessment evidence')
p('The project passed 50 checks through XAMPP Apache and MySQL. The runner submits real forms and checks the records they create. It covers the path from signup and login to saved progress and logout. Separate checks cover password hashes, CSRF, session expiry, account isolation, escaping and login limits. SQL injection and private-folder access were checked too. The runner removes its own test accounts afterwards. PHP reported no syntax errors in the source files.')
p('The screenshots show the home page, signup, login and dashboard. The layouts were checked on phone, tablet and desktop widths. A request made while MySQL was stopped returned HTTP 503. Once MySQL was running again, the database checks passed. These checks cover the classroom project. Public hosting would need a separate security review.')
rows = [['Rubric criterion', 'Marks', 'Implementation evidence'],
    ['Logo display', '2', 'Shared header with official logo'],
    ['Login and signup', '20', 'Validated forms and hashed accounts'],
    ['Logout', '10', 'POST, CSRF and session destruction'],
    ['Dashboard design', '5', 'Goal, progress, lessons and activity'],
    ['Security and experience', '5', 'Protected requests and responsive UI'],
    ['Footer', '3', 'Student fields from site configuration'],
    ['Screenshot documentation', '5', 'Home screenshot and five-page report']]
cells = [[Paragraph(escape(cell), ParagraphStyle('Cell', parent=body, leading=15, spaceAfter=0)) for cell in row] for row in rows]
table = Table(cells, colWidths=[146,60,277], repeatRows=1)
table.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),colors.HexColor('#e9e9e6')),('GRID',(0,0),(-1,-1),.5,colors.HexColor('#d9d9d9')),('VALIGN',(0,0),(-1,-1),'MIDDLE'),('LEFTPADDING',(0,0),(-1,-1),8),('RIGHTPADDING',(0,0),(-1,-1),8),('TOPPADDING',(0,0),(-1,-1),7),('BOTTOMPADDING',(0,0),(-1,-1),7)]))
story.append(table)
markdown.extend(['| Criterion | Marks | Evidence |','| --- | --- | --- |']+['| '+' | '.join(row)+' |' for row in rows[1:]]+[''])
h('Sources and credits')
link('Django Girls Freetown community page', 'https://djangogirls.org/de/freetown1/')
link('Official Django Girls logo resources', 'https://github.com/DjangoGirls/resources')
link('PHP manual on password hashing', 'https://www.php.net/manual/en/function.password-hash.php')
p("Django Girls owns the logo. Its resource terms ask non-organisers to get permission before using it. The illustration and layout were created for this project. AI assistance was used for the code and this report. Before submission, the code needs to be reviewed and the lecturer's assessment form needs the student's own signature.")

def footer(canvas, doc):
    canvas.saveState()
    canvas.setFont('Tahoma', 12)
    canvas.drawString(56, 28, 'Salone Studio  |  Assignment 1')
    canvas.drawRightString(A4[0]-56, 28, f'{doc.page} / 5')
    canvas.restoreState()

out = root / 'docs/assignment-report.pdf'
document = SimpleDocTemplate(str(out), pagesize=A4, rightMargin=56, leftMargin=56, topMargin=42, bottomMargin=50, title='Salone Studio PHP Authentication System and Dashboard', author=config['student_name'])
document.build(story, onFirstPage=footer, onLaterPages=footer)
(root / 'docs/assignment-report.md').write_text('\n'.join(markdown),encoding='utf-8',newline='\n')
print('Created', out)
