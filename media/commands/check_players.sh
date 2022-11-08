PID_MENCODER=`pidof mencoder`
PID_MPLAYER=`pidof mplayer`
PID_XMMS2D=`pidof xmms2d`

[ -z $PID_MENCODER ] || echo  "\"MENCODER\": $PID_MENCODER"
[ -z $PID_MPLAYER ] || echo  "\"MPLAYER\": $PID_MPLAYER"
[ -z $PID_XMMS2D ] || echo  "\"XMMS2D\": $PID_XMMS2D"

#echo " "

