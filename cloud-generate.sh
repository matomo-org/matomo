
source cloud-config.sh

for I in $(seq 1 $END); do
    for ((n=1;n<$(( ( RANDOM % 5 ) + 1 ));n++));
    do
        echo "Generating traffic for Piwik instance $I and website id = $n..."
        echo ""
        CMD="./console visitorgenerator:generate-visits --idsite=$n --limit-fake-visits=$(( ( RANDOM % 50 )  + 1 )) --no-logs --piwik-domain=http://test$I.piwik.pro"
        echo $CMD
        $CMD
        echo ""

    done

done

