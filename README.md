# A Web UI frontend for YouTube-DL
This is a small project that creates a simple and easy to use user interface for downloading content with YouTube-DL (or yt-dlp).

# How it looks

## Landing page
![Landing Page](/images/landing.png)
## Downloading
![Downloading](/images/downloading.gif)

# What it offers
- A default option to download a video
- A second drop down option for audio only
- Downloads all content in highest resolution/quality
- Log file to see what IP, Browser, OS and Video was downloaded
- The support for all YouTube-DL compatible websites

# How to use
After setting up Apache, I recommend the following steps for getting the program working. All this was tested on Debian 11.

1. Install PHP

```
sudo apt update && sudo apt install php
```

2. Install YouTube-DLP (yt-dlp)

```
sudo curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp
sudo chmod a+rx /usr/local/bin/yt-dl
```

3. Install yt-dlp dependencies. We use gallery-dl for instagram videos, I found it works a bit better

```
sudo apt update && sudo apt install gallery-dl ffmpeg atomicparsley
```

4. Move the project to a location on your webserver. In this case, I am moving it to /public/yt-dl which will allow users to acces it at www.example.com/yt-dl

```
git clone https://github.com/wailord284/YouTube-DL-WebUI
mv YouTube-DL-WebUI /var/www/example.com/public/yt-dl
```

5. Grant permissions. Your webserver may require permissions to run these files

```
sudo chown -R www-data:www-data /var/www/example.com/public/yt-dl
```

6. Make a temp directory for videos and the log file

- Find videoDIR within the ytdl.sh file. Change this to any location you want for the videos to be downloaded
- Find logDIR within the ytdl.sh file. Change this to a location for a log file to be stored
- Find $video_path within the video_download.php file. Change this to the same location as ytdl.sh videoDIR
- Find $script_path within the video_download.php file. Change this to the location you put the project (yt-dl in the above example)
- Finally, create a log file and directory where you just specified. Change the permissions the same way we did previously
```
sudo chown -R www-data:www-data videos
sudo chown -R www-data:www-data ytlog.txt
```

7. Optional - Create a cronjob to clear old videos
- In this example, replace /home/alex/videos/* with the location of your video directory

```
sudo systemctl enable cron
sudo crontab -e
0 0 * * * rm -r /home/alex/videos/*
```

8. Optional - Edit PHP options
- If you download longer videos, PHP execution time may not be long enough. You may also need additional RAM
- In Debian 11, this file is located here: /etc/php/7.4/apache2/php.ini
    * Change max_execution_time = 30 to max_execution_time = 300
    * Change memory_limit = 128M to memory_limit = 1024M
