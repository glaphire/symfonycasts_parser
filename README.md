# symfonycasts_parser
Application automated video downloading from www.symfonycasts.com

## Setup
Current Chromedriver version in the project is 95.0.4638.69.

Download version of Chromedriver compatible with your local Chrome Browser

   https://chromedriver.chromium.org/downloads

Put chromedriver in bin directory (replace current chromedriver file).
Run

    $ sudo chmod +x bin/chromedriver
    
Copy .env file to .env.local and fill variables

    #path to existing directory for future downloads
    DOWNLOAD_DIR_ABS_PATH=/path/to/downloads/directory
    
    #directory name for current unfinished downloads
    TEMP_DOWNLOAD_DIR_NAME=current_download_dir
    
    #absolute path to Chrome profile
    PROFILE_DIRECTORY_ABS_PATH=/home/user/.config/google-chrome/Default
    
    #Symfonycasts account credentials
    SYMFONYCASTS_LOGIN=login
    SYMFONYCASTS_PASSWORD=password
    
Run in one tab

    ./bin/chromedriver --port=4444
    
Run in second tab

    php bin/console app:parse-course <abs url to symfonycasts course>
    
If your download has been failed, you may restart from certain lesson

    php bin/console app:parse-course <abs url to symfonycasts course> <lesson-number>