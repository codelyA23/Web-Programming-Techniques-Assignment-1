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
h('Project purpose and local context')
p('Salone Studio is an independent student prototype for a Django Girls learning community in Sierra Leone. It combines account registration, secure login, a personalised dashboard and logout with a practical web learning path. The dashboard uses saved account data and lesson completion records, giving personalised information a useful purpose.')
p('The official Django Girls Freetown page documents a workshop held in October 2017. That local connection informs the concept. The portal does not claim to announce a current workshop, represent the organisation officially, or award qualifications. Its purpose is a student-built learning space with working authentication and account-specific information.')
h('Visual identity and design choices')
p('The official Django Girls logo appears at the top of every page. Orange references the organisation’s identity, while cream backgrounds, deep navy headings and white cards create an approachable learning space. The Salone Studio name, restrained Sierra Leone flag detail, Freetown coordinates and original coast-and-hills illustration establish a local identity.')
p('Bold headings, orange pill buttons, floating panels and soft shadows make the next step clear. Segoe UI and Tahoma provide local typography; a small Georgia accent remains in the footer. The five-page report uses embedded Tahoma at 12 points. No purchased theme, UI framework, third-party plugin or remote font service is used.')
p('The original SVG depicts two women making a website together. Floating notes and illustrated cards add depth with CSS. Cards lift on hover; reduced-motion preferences disable transitions. The project prioritises an encouraging learning experience and avoids invented community statistics or workshop bookings.')
page()

h('Home page screenshot')
p('Figure 1 shows the complete desktop home page captured from XAMPP Apache. Desktop and mobile screenshots are included with the repository.')
imgpath = root / 'docs/screenshots/home-desktop.jpg'
with PILImage.open(imgpath) as image:
    iw, ih = image.size
image_height = 615
image_width = image_height * iw / ih
story.append(Image(str(imgpath), width=image_width, height=image_height, hAlign='CENTER'))
markdown.extend(['![Complete home page](screenshots/home-desktop.jpg)', ''])
page()

h('Page structure and user experience')
p('The home page introduces the purpose and a three-step journey before asking visitors to register. A clear call to action opens signup. The same header, logo and student copyright footer continue across public and signed-in pages, making navigation predictable.')
p('Signup asks for a full name, email, learning goal, password and confirmation. Required fields and browser constraints give immediate guidance, while the server checks every submission independently. Invalid values return readable feedback without repopulating passwords. Duplicate email addresses are rejected, including case variations.')
p('After registration, the user is redirected to login with a confirmation message. Successful login opens a protected dashboard with the user’s name, learning goal, completion percentage and recent activity. A new account starts at zero completed lessons. The next-step card points to the first unfinished lesson.')
p('Four original lessons cover HTML, CSS, PHP and authentication security. Each contains explanations, a code example, a practice challenge and a server-checked question. A correct answer saves one completion record. Progress persists after logout; completing the same lesson twice does not increase the total. This is a learning check rather than a certificate.')
p('The profile page updates the learner’s name and goal. The resource shelf links to official Django Girls, MDN, PHP and GitHub materials. These open in a new tab, leaving the workspace available. Logout is a deliberate button action that ends the session and returns to login.')
h('Responsive and accessible presentation')
p('Desktop layouts pair text with illustration and use a sidebar for the workspace. On a phone, the hero, forms and cards stack, and the sidebar becomes compact navigation. Controls have labels, visible keyboard focus and clear errors. Completion is identified by text as well as colour. A skip link leads to main content, decorative icons are hidden from assistive technology and reduced-motion preferences are respected.')
p('Every form is handled by PHP without JavaScript. Local artwork and system fonts avoid external requests. The application runs from htdocs through XAMPP Apache; MySQL must be running for account and progress reads and writes.')
page()

h('Implementation and account security')
p('Plain PHP handles requests and renders HTML. Shared bootstrap and view functions avoid repeating authentication, database and layout logic. Lesson data is an array; loops render the learning path, while conditional statements select completed states, validation feedback and the next lesson. This demonstrates variables, arrays, functions, loops, forms and sessions.')
p('PDO uses native prepared statements to connect to XAMPP MySQL/MariaDB. Import database/schema.sql through phpMyAdmin before use. Users, progress and activity are linked by foreign keys; a unique email constraint prevents duplicate accounts. All database operations require the running MySQL server.')
h('Password and database protection')
p('Registration stores the output of password_hash with PASSWORD_DEFAULT. Login uses password_verify, and password_needs_rehash upgrades hashes when necessary. Plain passwords are not stored in the database or activity history. The password length is limited to 72 bytes to avoid bcrypt truncation.')
p('Prepared statements keep user values separate from SQL instructions. The e helper applies htmlspecialchars before account values enter HTML, preventing a stored name from becoming executable markup. Account IDs for progress and profile queries come from the authenticated session, not a submitted user ID.')
h('Session and request protection')
p('Successful login regenerates the session identifier. Session cookies use HttpOnly and SameSite Lax; the Secure flag is enabled when the request uses HTTPS. Protected pages redirect anonymous users to login, and idle sessions expire after 30 minutes. Logout accepts POST only, verifies a session-bound CSRF token, clears session data, expires the cookie and destroys the stored session.')
p('Registration, login, profile changes, progress and logout all validate a CSRF token. Login failures are limited through persistent account and IP buckets. Generic login errors avoid confirming an email’s existence. Security headers restrict scripts, framing and content interpretation. Responses containing account data are marked no-store.')
p('The project is installed in C:/xampp/htdocs/salone-studio. Apache denies HTTP access to private folders using .htaccess. MySQL stores account records; storage holds private sessions excluded from Git. PHP rejects execution outside Apache. MySQL outages return HTTP 503 without a fallback database. Public hosting would require HTTPS and restricted database credentials.')
page()

h('Verification and assessment evidence')
p('The PHP integration suite passed 50 checks through running XAMPP Apache and MySQL. It submits real HTTP forms and verifies records, then removes its own test data. Coverage includes registration, hashes, session rotation, private-folder protection, CSRF, progress, account isolation, escaping, logout, expiry, throttling and SQL injection resistance. All PHP files passed syntax checks.')
p('Updated screenshots document the Apache-served home, signup, login and dashboard. The visual refresh was checked at phone, tablet and desktop widths. An actual request while MySQL was stopped returned HTTP 503; database-dependent work resumed only after MySQL restarted. These checks provide coursework evidence, not a production security certification.')
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
h('Sources and asset credits')
link('Django Girls Freetown community page', 'https://djangogirls.org/de/freetown1/')
link('Official Django Girls logo resources', 'https://github.com/DjangoGirls/resources')
link('PHP manual on password hashing', 'https://www.php.net/manual/en/function.password-hash.php')
p('The logo remains Django Girls’ property; its resource terms request permission for non-organiser reuse. Illustration and layout are original to this project. AI assistance was used and should be disclosed according to university policy. The footer and report identify Mohamed Sow, student ID 905004376. The student must review the implementation and personally sign the lecturer’s assessment form.')

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
