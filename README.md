# Rekry Group Likeit Link

This project is a plugin made specifically to be used with WordPress to forward job application forms to Likeit API. The purpose of this is to enhance the user experience of new and returning Rekry Group Oy job applicants. It does this by forwarding the applicant data from CF7 form and sending it directly to Likeit api. This way applicants do not have to visit the Likeit page to fill the original complicated form. 

## Requirements and Limitations

### Requirements
- WordPress site
- Required plugins: Contact Form 7, Contact Form CFDB7, WPCode Lite
- Optional plugins (these are not necessarily required but further improve the user experience): Column Shortcodes, Popup Maker
- Environmental Variables:  
```
LIKEIT_KEY= <Your Likeit API key>
LIKEIT_URL= <Your Likeit API url>
PATH_START= <Either '/rekrygrouptest/api' or '/api'. Exists for easier intergration from dev env to production env>
```

### Limitations
- CF7 job application form (Tyonhakulomake) fields should <strong>NOT BE EDITED</strong>. Changing the names of the fields will cause issues. Also any new fields added will not be recognized by this plugin. This is due to the way this plugin is created. It only expects to receive data that is expected by the Likeit api.
- This plugin is not to be used in any other use case than forwarding Rekry Group job application data from Wordpress to Likeit. Likeit api differs from company to company and therefore this solution cannot be used to link form submission to other APIs.