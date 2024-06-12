# Rekry Group Likeit Link

This project is a plugin made specifically to be used with WordPress to forward job application forms to Likeit API. The purpose of this is to enhance the user experience of new and returning Rekry Group Oy job applicants. It does this by forwarding the applicant data from CF7 form and sending it directly to Likeit api. This way applicants do not have to visit the Likeit page to fill the original complicated form. 

## Requirements and Limitations

### Requirements
- WordPress site
- Required plugins: Contact Form 7, Contact Form CFDB7, WPCode Lite
- Optional plugins (these are not necessarily required but further improve the user experience): Column Shortcodes, Popup Maker
- Environmental Variables:
<code>
LIKEIT_KEY=
LIKEIT_URL=
PATH_START=
</code>