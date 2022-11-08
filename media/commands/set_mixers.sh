### set_mixers.sh
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

$AMIXER sset $CTL_MASTER $1%
