#!/bin/bash
# intro (3.5 s) + guía grabada + cierre (3.5 s), con fundidos y la música andina de fondo
rol=$1; out=/home/claude/z/Tampu/public/videos/guia-$rol.mp4
D=$(ffprobe -v error -show_entries format=duration -of csv=p=0 $rol.webm)
M=$(python3 -c "print(round($D - 0.8, 2))")
T=$(python3 -c "print(round(3.5 + $M - 0.6 + 3.5 - 0.6, 2))")
ffmpeg -y -loglevel error \
  -loop 1 -framerate 30 -t 3.5 -i intro_$rol.png \
  -ss 0.8 -i $rol.webm \
  -loop 1 -framerate 30 -t 3.5 -i final.png \
  -stream_loop -1 -i /home/claude/musica/andina.wav \
  -filter_complex "\
[0:v]scale=1280:720,setsar=1,fps=30,format=yuv420p,zoompan=z='1+0.0006*on':d=1:s=1280x720:fps=30[a];\
[1:v]scale=1280:720,setsar=1,fps=30,format=yuv420p,setpts=PTS-STARTPTS[b];\
[2:v]scale=1280:720,setsar=1,fps=30,format=yuv420p[c];\
[a][b]xfade=transition=fade:duration=0.6:offset=2.9[ab];\
[ab][c]xfade=transition=fade:duration=0.6:offset=$(python3 -c "print(round(2.9 + $M - 0.6, 2))"),scale=960:540:flags=lanczos[v];\
[3:a]atrim=0:$T,volume=0.45,afade=t=in:d=1.5,afade=t=out:st=$(python3 -c "print(round($T-2.5,2))"):d=2.5[m]" \
  -map "[v]" -map "[m]" -c:v libx264 -preset veryfast -crf 28 -pix_fmt yuv420p -c:a aac -b:a 96k -movflags +faststart -t $T $out
echo "$rol $(ffprobe -v error -show_entries format=duration -of csv=p=0 $out)s $(du -h $out | cut -f1)"
