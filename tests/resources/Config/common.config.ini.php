[Category]
key2 = valueCommon

; This should not trigger an error if INI_SCANNER_RAW is used
key3 = "${@piwik(crash))}"

[GeneralSection]
password = passwordCommonShouldNotBeOverriden

[TestOnlyInCommon]
value = commonValue

[TestArray]
installed[] = plugin777

[Tracker]
commonConfigTracker = commonConfigTrackerValue