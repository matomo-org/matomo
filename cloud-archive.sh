source cloud-config.sh

for I in $(seq 1 $END);
do
    echo "Running enterprise:dashboard..."
    echo ""

    ./console enterprise:dashboard --piwik-domain=http://test$I.piwik.pro

    echo "Running archiving for: test$I.piwik.pro... with --force-idsites"
    echo ""
    echo ""
    ./console enterprise:archive --piwik-domain=http://test$I.piwik.pro --force-idsites=1
    echo ""
    echo ""

done



