### get_mixers.sh
# returns mixer control values (%) in the following order:
# 1. MASTER (mono)
# 2. PCM Left
# 3. PCM Right
# 4. Front Left
# 5. Front Right

CTL_MASTER=Master
CTL_PCM=PCM
CTL_FRONT=Front

AMIXER=/usr/bin/amixer
# sudo not needed!

#for CTL in $CTL_MASTER $CTL_PCM $CTL_FRONT;
CTL=$CTL_MASTER
#do
	$AMIXER sget $CTL | grep 'Playback\ [0-9]\+\ \[\([0-9]\+\)%\]' | grep -o '\[[0-9]\+%\]' | sed -nr 's/\[([0-9]{1,3})%\]/\1/p'
#done
