#FILE=$1
[ -e /tmp/mp_in.fifo ] || mkfifo /tmp/mp_in.fifo

export HOME=/home/pt
export DISPLAY=:0

#echo $FILE |
mplayer -slave -quiet -input file=/tmp/mp_in.fifo -playlist /tmp/NEXT >/dev/null 2>&1 &
