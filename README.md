# Rekry Group Likeit Link

This project is a plugin made specifically to be used with WordPress to forward job application form data to Likeit API. The purpose of this is to enhance the user experience of new and returning Rekry Group Oy job applicants. It does this by forwarding the applicant data from CF7 form and sending it directly to Likeit API. This way applicants do not have to visit the Likeit page to fill the original complicated form. 

## Requirements and Limitations

### Requirements
- <strong>PHP version 7.4</strong>
- WordPress site
- Required plugins: <strong>Contact Form 7, Contact Form CFDB7, WPCode Lite</strong>
- Optional plugins (these are not necessarily required but further improve the user experience): Column Shortcodes, Popup Maker
- <strong>IMPORTANT NOTE:</strong> When creating your contact form do not name it anything other than <code>Tyonhakulomake FI</code> or <code>Tyonhakulomake ENG</code>.
- Required Environmental Variables:  
```
LIKEIT_KEY= <Your Likeit API key>
LIKEIT_URL= <Your Likeit API url>
PATH_START= <Either '/rekrygrouptest/api' or '/api'. Exists for easier intergration from dev env to production env>
```
The variables above are not included in this repository for security reasons.

### Limitations
- CF7 job application form (Tyonhakulomake) fields should <strong>NOT BE EDITED</strong>. Changing the names of the fields will cause issues. Also any new fields added will not be recognized by this plugin. This is due to the way this plugin is created. It only expects to receive data that is expected by the Likeit API.
- This plugin is not to be used in any other use case than forwarding Rekry Group job application data from Wordpress to Likeit. Likeit API differs from company to company and therefore this solution cannot be used to link form submission to other APIs.

## Usage
### Installation
1. In the wp-config.php file of your site add the environmental constant variables mentioned before (LIKEIT_KEY, LIKEIT_URL, PATH_START). The wp-config.php file should be located in a folder called public or public_html (cPanel). An example of defining a variable in the wp-config.php file:  
```
define( 'name_goes_here', 'value_goes_here' );
```
2. Go to <code>public/wp-content/plugins</code> folder and create a new folder called cf7-trap-api.
3. In this new cf7-trap-api folder copy and paste the <code>cf7-trap-api.php</code>, <code>composer.json</code>, <code>composer.lock</code> and the <code>vendor</code> directory located in this repository.
4. Now you can see a plugin called CF7 Form Trap installed in your plugins on the website. Activate it if it is not activated by default.

### Configuration
- Contact Form 7 base forms (<strong>NOTE:</strong> DO NOT change the names of the fields): <code>/required code snippets/tyonhaku-FI-shortcode</code> & <code>/required code snippets/tyonhaku-ENG-shortcode</code>
- <strong>NOTE:</strong> remember to include <code>skip_mail: on</code> in the Additional settings (lisäasetukset) section/tab of your form. Otherwise an email will be sent.
- Create a popup if it does not exists. In the popup editor paste the code from <code>/required code snippets/TyonhakuPopup.html</code>
- In WPCode Lite add these JavaScript code snippets: <code>/required code snippets/apply-button-functionality.js</code> (insert method: shortcode), <code>/required code snippets/update-file-input-labels.js</code> (insert method: auto insert -> sitewide footer) & <code>/required code snippets/FormLanguageSwitch.js</code> (insert method: shortcode)
- In the custom css of the WordPress site add the code from <code>/required code snippets/page-custom-css.css</code>

<br><br>
<hr>
<br>

![Rekry Group Logo](https://www.rekrygroup.fi/wp-content/uploads/logo6.png)