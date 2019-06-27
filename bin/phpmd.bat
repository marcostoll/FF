@ECHO OFF
SET PROJECT_HOME=C:\dev\ffe\fastforward\FF
vendor\bin\phpmd %PROJECT_HOME%\src xml cleancode,codesize,controversial,design,naming,unusedcode --strict --reportfile %PROJECT_HOME%\logs\phpmd\report.xml
