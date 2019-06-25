@ECHO OFF
SET PROJECT_HOME=C:\dev\ffe\fastforward\FF
php -dxdebug.coverage_enable=1 %PROJECT_HOME%\vendor\phpunit\phpunit\phpunit --coverage-xml %PROJECT_HOME%\logs\phpunit --configuration %PROJECT_HOME%\tests\testsuite.xml --teamcity