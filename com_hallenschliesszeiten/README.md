# Hallenschließzeiten 2.0.0 - Joomla 6

Die gemeinsame Komponente verwaltet Schließzeiten und Halleninfos. Die Tabellen #__ttc_hallenschliesszeiten und #__ttc_halleninfos sowie ihre vorhandenen Spalten und Datensätze bleiben unverändert. Nur fehlende Tabellen werden mit dem bisherigen Schema angelegt. Das Halleninfos-Modul und die Hallenbuchung lesen weiterhin dieselben Tabellen.

Installation bei vorhandenen Komponenten:
1. com_hallenschliesszeiten-2.0.0.zip als Update installieren.
2. Berechtigungen unter Hallenschließzeiten > Optionen prüfen. Es gelten nun die Rechte dieser Komponente für beide Bereiche; abweichende alte Halleninfos-Rechte werden nicht automatisch übernommen.
3. VOR dem Entfernen der alten Halleninfos-Komponente com_halleninfos-2.6.0-datenerhalt.zip als Update installieren. Diese Übergangsversion entfernt das bisherige DROP TABLE bei Deinstallation. Danach kann die alte Komponente entfernt werden.
4. mod_halleninfos-2.1.2.zip als Modulupdate installieren, sofern dieses Modul verwendet wird.

Vorhandene Frontend-Menüeinträge für Halleninfos werden auf die gemeinsame Komponente umgestellt; Menü-ID, Alias, Zugriff und Parameter bleiben erhalten. Direkte URLs, die com_halleninfos enthalten, sowie eigene Template-Overrides müssen manuell angepasst werden. Bestehende Hallenschließzeiten-Menüs bleiben gültig.

Backend: option=com_hallenschliesszeiten&view=schliesszeiten bzw. view=halleninfos.
Frontend: view=halleninfos, view=halleninfo und view=halleninfoform.

Auch bei der Deinstallation der neuen Komponente bleiben beide Fachtabellen erhalten.
Schließzeit-Ende darf nicht vor Beginn liegen, Textlängen entsprechen dem bestehenden Schema. Pro Datum bleibt eine Halleninfo zulässig.

Prüfung: PHP-Syntax, XML/JSON, Klassenladen mit dem vorhandenen Joomla-6.0.0-Core und gezielte Prüfungen für Datumsvalidierung, Menüumstellung und unveränderte Tabellenschemata. Ein Installationstest mit einer laufenden Joomla-Datenbank steht aus.
