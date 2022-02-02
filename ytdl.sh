#!/bin/bash
#Set video directory
videoDIR="/home/alex/videos"
logDIR="/home/alex/ytlog.txt"
#Take user input from the php script
while [ -n "$1" ]; do

	case "$1" in

	-b)
		userBrowser="$2"
		shift ;;

	-o)
		userOS="$2"
		shift ;;

	-a)
		ipAddress="$2"
		shift ;;

        -f)
		subFolder="$2"
		shift ;;

	-t)
		videoType="$2"
		shift ;;

	-v)
		videoLink="$2"
		esac
		shift

done
#Create log file to see how many videos have been downloaded
date=$(date)
echo "$date : $ipAddress : $userBrowser : $userOS : $subFolder : $videoType : $videoLink" >> "$logDIR"
#Make sure filename is less than 255 characters
LC_ALL=en_US.UTF-8 videoName=$(yt-dlp --get-filename "$videoLink" --output "%(title)s" | cut -c1-120 | sed -e 's/$/.mp4/')

#Setup instagram workaround - yt-dlp isnt working with instagram for some reason :/
instagramVideo=$(echo "$videoLink" | grep -o "instagram.com")
mkdir -p "$videoDIR"/"$subFolder"
#We use gallery-dl to get just the url, then wget it
if [ "$instagramVideo" = instagram.com ]; then
	instagramVideoLink=$(gallery-dl --get-urls "$videoLink" | grep mp4)
	wget "$instagramVideoLink" -O "$videoDIR"/"$subFolder"/instagramvideo.mp4

#Download audio or video based on $videoType
elif [ "$videoType" = video ] ; then
	LC_ALL=en_US.UTF-8 yt-dlp --add-metadata --prefer-ffmpeg --metadata-from-title "%(title)s" -f 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/mp4' --output "$videoDIR"/"$subFolder"/"$videoName" "$videoLink"
elif [ "$videoType" = audio ] ; then
	LC_ALL=en_US.UTF-8 yt-dlp -x -f bestaudio/best --format m4a --add-metadata --metadata-from-title "%(title)s" --prefer-ffmpeg  --output "$videoDIR"/"$subFolder"/"$videoName" "$videoLink"
elif [ "$videoType" = thumbnail ] ; then
	LC_ALL=en_US.UTF-8 yt-dlp --write-thumbnail --skip-download --output "$videoDIR"/"$subFolder"/"%(title)s" "$videoLink"
else
	touch "$videoDIR"/error.txt
	echo "No valid videoType was specified" >> "$videoDIR"/error.txt
	exit 0
fi
