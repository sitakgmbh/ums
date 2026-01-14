# Changelog

Alle Änderungen an diesem Projekt werden in diesem Dokument festgehalten.

---

## [1.1.4] – 16.01.2025

#### Neuigkeiten
- Die Seite ***SAP Export*** wurde hinzugefügt, welche den aktuellen SAP Export aus der Datenbank darstellt. Zudem können archivierte SAP Exporte als Tabelle angezeigt oder heruntergeladen werden.
- Über die Detailseite eines Active Directory Benutzers kann nun eine Seite zum Ändern von Passwörtern in AD und KIS erreicht werden.

#### Änderungen
- Wenn eine Eröffnung erstellt wird, ist die Option ***Telefonie*** standardmässig aktiv, da meistens vergessen wurde, eine Telefonnummer zu hinterlegen. Die Option ***Unpersönliche Telefonummer*** ist vorausgewählt.
- Im Auftrag ***Verarbeitung KIS*** wird nun ein Hinweis bzgl. 'TVER' angezeigt.
- Das Mail-Tool zeigt nun auch archivierte Anträge an.

#### Bugfixes
- Vorname und Nachname wurden beim Erstellen einer Eröffnung nicht getrimmt, was vereinzelt zu überflüssigen Leerzeichen führte.

## [1.1.3] – 05.01.2025

#### Änderungen
- Der Task ***check:sap-ad-mappings*** prüft nur noch vorhandene AD-Benutzer (is_exisiting = true), da es vorkommt, dass AD-Benutzer erstellt, gelöscht und nochmals erstellt werden.

## [1.1.2] – 31.12.2025

#### Änderungen
- Es kam immer wieder vor, dass bei Anträgen ein falscher Referenzbenutzer ausgewählt wurde. In den Formularen ***Eröffnung*** und ***Mutation*** wurde deshalb bei ***PC-Berechtigungen übernehmen von*** ein Hinweis eingebaut.

## [1.1.1] – 14.11.2025

#### Neuigkeiten
- Der Abgleich SAP ↔ AD wird neu von UMS erledigt.

## [1.1.0] – 01.11.2025

#### Neuigkeiten
- UMS wurde auf Laravel migriert. 

## [1.0.22] – 02.10.2025

#### Änderungen
- Beim Versand des ***Info-Mails***, wird das HR nur noch ins CC gesetzt, wenn ein KIS-Benutzer beantragt wurde.
- Die Passwortlänge wurde von ***10*** auf ***8*** Zeichen reduziert.

## [1.0.21] – 13.08.2025

#### Änderungen
- Beim Ausführen der Aufgabe ***E-Mail-Adresse und Aliase anpassen*** wird vorab geprüft, ob Änderungen notwendig sind. Falls nicht, wird die Aufgabe als erledigt markiert, ohne Änderungen vorzunehmen.
- Beim Archivieren eines Antrags wird der Benutzer, der den Vorgang in UMS ausführt, im dazugehörigen OTOBO-Ticket als ***Owner*** eingetragen.

#### Bugfixes
- Die Detailansicht einer ***Mutation*** zeigt nun korrekt an, ob sie archiviert ist (analog ***Eröffnungen*** und ***Austritte***). Der dazugehörige Button wird deaktiviert.

## [1.0.20] – 06.08.2025

#### Änderungen
- Bei der Bestellung eines ***SAP-Benutzers*** oder ***SAP-Leistungserbringers*** werden neu auch Benutzername, Telefonnummer, E-Mail-Adresse sowie Antragsteller und E-Mail Antragsteller übermittelt.

#### Bugfixes
- Ein Variablenzugriff, der nicht sauber abgefangen wurde, verursachte beim Bearbeiten von Mutationen eine PHP-Warnung. Das Problem wurde gelöst.
- Beim Ein- und Ausblenden von Archiveinträgen kam es unter gewissen Umständen vor, dass gar keine Anträge mehr angezeigt wurden. Die Ursache war, dass beim Ausblenden der Archiveinträge die Tabelle neu gerendert werden musste.
- Das Anzeigen/Bearbeiten von Anträgen bzw. das Laden der Daten für das Formular wurde robuster gestaltet, um Race Conditions zu vermeiden.

## [1.0.19] – 05.08.2025

#### Änderungen
- Beim Versand von Aufträgen bzgl. Zutrittsrechten ist das Eintrittsdatum im Betreff der E-Mail ersichtlich.

## [1.0.18] – 22.07.2025

#### Änderungen
- Bei der Bestellung eines ***SAP-Benutzers*** bei Eröffnungen oder Mutationen, wird zusätzlich eine Kopie an ***RCPT_SAP_CC*** gesendet. Bei der Bestellung eines ***SAP-Leistungserbringers*** bei Eröffnungen oder Mutationen, wird ein E-Mail an ***RCPT_SAP_LEI_CC*** gesendet.
- Wenn ***SAP-Leistungserbringer*** bei Eröffnungen oder Mutationen ausgewählt wurde, führt dies wieder dazu, dass Aufträge versendet werden müssen (siehe Version 1.0.14).

#### Bugfixes
- Wenn bei der Verarbeitung eines Antrags Auftragsmails versendet werden müssen, wird - sofern ein SAP-Benutzer bestellt wurde - das Bestelldatum des SAP-Benutzers in das extensionAttribute4 des AD-Benutzers eingetragen (siehe Änderung Version 1.0.14). Bisher war es durch das Neuladen der Detailseite eines Antrags möglich, z. B. Auftragsmails zu versenden, obwohl der AD-Benutzer noch nicht existierte. Dies führte beim Versuch, das Bestelldatum des SAP-Benutzers zu speichern, zu einem Fehler. Ab sofort ist es nicht möglich, eine Aktion durchzuführen, sofern der AD-Benutzer noch nicht erstellt wurde (ad_status != 2).

## [1.0.17] – 06.07.2025

#### Änderungen
- Ausgewählte Eigenschaften eines Antrags (z. B. ***ad_status*** oder ***kis_status***) können über die Detailsseite eines Antrags bearbeitet werden.

#### Bugfixes
- Behebt einen Fehler, der das Erstellen von Mutationen verhinderte. Verursacht wurde das Problem durch einen Tippfehler (eingeführt in Version 1.0.16).

## [1.0.16] – 30.06.2025

#### Änderungen
- Bei einer Eröffnung wird bei der Eingabe von Vorname oder Nachname direkt geprüft, ob es sich um einen Wiedereintritt handelt und ob im Vornamen oder Nachnamen ein Abstand (und ein zweiter Name) enthalten ist. Zuvor erfolgte die Prüfung beim Absenden des Formulars. Zudem wird geprüft, ob bereits eine Eröffnung oder eine Mutation mit dem Vor- oder Nachnamen existiert. Somit wird der Antragsteller schon früh beim Ausfüllen des Formulars darauf hingewiesen, dass ein Antrag möglicherweise nicht nötig ist.

## [1.0.15] – 17.06.2025

#### Änderungen
- Bei Eröffnungen und Mutationen werden neu beim Erstellen und Bearbeiten die AD-Gruppenmitgliedschaften des Benutzers abgefragt und im Antrag gespeichert, der unter ***Berechtigungen übernehmen von*** ausgewählt wurde. Dies stellt sicher, dass der aktuelle Stand der Berechtigungen zum Zeitpunkt der Erstellung des Antrags verwendet wird.
- Wenn bei einer Eröffnung der AD-Benutzer erstellt werden soll, werden Benutzername und E-Mail-Adresse nicht mehr neu generiert, wenn es sich um einen Wiedereintritt handelt.
- Wenn ***SAP-Leistungserbringer*** bei Eröffnungen oder Mutationen ausgewählt wurde, führt dies nicht mehr dazu, dass Aufträge versendet werden müssen.

## [1.0.14] – 22.05.2025

#### Neuigkeiten
- KIS-Benutzer können via UMS direkt in Orbis NICE erstellt und mutiert werden.

#### Änderungen
- Beim Anpassen der E-Mail-Adresse wird nur noch auf vorhandene E-Mail-Adressen im AD geprüft. Zuvor wurde auch geprüft, ob die E-Mail-Adresse in einem Antrag in UMS existiert.
- Wenn die Option ***SAP-Leistungserbringer*** ausgewählt wurde, wird neu keine E-Mail an das KSGR geschickt.
- Bei der Bestellung eines SAP-Benutzers wird das Bestelldatum (Versand Auftrag) im extensionAttribute4 des AD-Benutzers hinterlegt (Format ***yyyyMMdd***). Dies gilt für Eröffnungen und Mutationen.

## [1.0.13] – 06.05.2025

#### Änderungen
- Das E-Mail ***Auftrag Mutation SAP-Benutzer*** enthält nun umfangreiche Informationen (Arbeitsort, Unternehmenseinheit, Abteilung, Funktion inkl. Deklaration Änderungen).

## [1.0.12] – 12.05.2025

#### Neuigkeiten
- UMS ist nun mit der User Provisioning API von Orbis verbunden.

## [1.0.11] – 06.05.2025

#### Änderungen
- Das E-Mail ***Auftrag Mutation SAP-Benutzer*** enthält nun umfangreiche Informationen (Arbeitsort, Unternehmenseinheit, Abteilung, Funktion inkl. Deklaration Änderungen).

## [1.0.10] – 05.05.2025

#### Neuigkeiten
- Da das Laden eines Antrags je nach Umfang ein bis zwei Sekunden dauern kann, kommt es gelegentlich vor, dass Benutzer versehentlich mehrfach auf „Bearbeiten“ oder „Details“ klicken. Dies führt dazu, dass ein Antrag doppelt geöffnet wird – technisch unkritisch, aber aus Sicht der Benutzererfahrung störend. Um dem vorzubeugen, wird der jeweilige Button beim ersten Klick deaktiviert und durch einen Spinner ersetzt, bis der Ladevorgang abgeschlossen ist.

#### Änderungen
- Bei den Mutationen kann neu der Titel angepasst werden.

#### Fehlerbehebungen
- Die Gruppenausnahmen (siehe Changelog Version 1.0.6) wurden korrigiert. Die SEC-Gruppen hatten ein G- statt L-Präfix.

## [1.0.9] – 01.05.2025

#### Änderungen
- Wenn bei einer Mutation die Optionen ***Abteilung anpassen*** oder ***Funktion anpassen*** ausgewählt wurde, muss die Aufgabe ***KIS*** erledigt werden.
- Wenn bei einer Mutation die Optionen ***Abteilung anpassen***, ***Funktion anpassen*** oder ***Zweite Abteilung*** ausgewählt wurden, wird dies in der Detailansicht angezeigt.
- Wenn bei einer Mutation ein ***Namenswechsel*** stattfindet, wird dies in der Detailansicht angezeigt.

## [1.0.8] – 29.04.2025

#### Änderungen
- Sämtliche OTOBO-Tickets, welche über UMS erstellt werden, werden in der Queue ***Personal*** erstellt. Grund dafür ist, dass bei gewissen Aktionen (z. B. Änderung Ticket) eine Benachrichtigung an den Benutzer geschickt wird, was nicht gewünscht ist.
- Während der Übermittlung eines Antrags (erstellen und aktualisieren) wird der Button ***Speichern*** deaktiviert und zeigt den Text ***Bitte warten..***. Grund dafür ist, dass es vorkam, dass Anträge doppelt erstellt werden, weil während der Übermittlung eine weitere Übermittlung gestartet wurde. Dies kann vorkommen, da die Übermittlung zwei, drei Sekunden dauern kann (u. a. wegen Abfragen in OTOBO).

## [1.0.7] – 28.04.2025

#### Änderungen
- Austritte werden in der OTOBO-Queue ***Personal*** erstellt. Eröffnungen und Mutationen werden nach wie vor in der Queue ***Servicedesk*** erstellt.

## [1.0.6] – 10.04.2025

#### Änderungen
- Ausgeführte Aktionen werden detaillierter in OTOBO protokolliert.
- Anpassung Berechtigungen bei Mutationen: Wenn eine zweite Abteilung ausgewählt wurde, ist standandardmässig die Option ***Berechtigungen ergänzen*** ausgewählt, sonst ***Berechtigungen überschreiben***.
- Berechtigungen übernehmen bei Mutationen muss nun explizit aktiviert werden, analog Arbeitsort, Funktion etc.
- Wenn sich Abteilung oder Funktion ändert oder eine zusätzliche Abteilung ausgewählt wurde, muss eine Person angegeben werden, von der die Berechtigungen übernommen werden sollen.
- Wenn die E-Mail-Adresse bei der Verarbeitung einer Mutation geändert wurde, wird sie auch in der UMS Datenbank aktualisiert, damit beim Info-Mail die korrekte (neue) E-Mail-Adresse ersichtlich ist.
- Arbeitsort, Unternehmenseinheit, Abteilung und Funktion muss immer ausgefüllt sein, wenn eine Mutation erstellt wird.
- Bei Austritten gibt es neu die Aktion ***LogiMen***.
- Folgende Gruppen werden beim Übernehmen von Berechtigungen ignoriert: ***G_APP_Novaalert***, ***G_APP_UCC***, ***L_SEC_M365-Licenses-EOP1***, ***L_SEC_Internet-RAS-Service***, ***L_SEC_M365_FAS_Citrix***

## [1.0.5] – 08.04.2025

#### Änderungen
- In der Detailansicht werden neu folgende Informationen angezeigt: Wiedereintritt und Telefonie
- Wird ein Eintrag via PATCH bearbeitet, wird korrekt geprüft, ob der Eintrag existiert. Die Fehlermeldungen wurden besser ausformuliert.
- Im Modal ***Info-Mail versenden*** wird bei Eröffnungen die E-Mail-Adresse der Bezugsperson anstelle des Antragstellers geladen. Bei Mutationen wird die E-Mail-Adresse des Benutzer geladen, für den eine Mutation bestellt wurde.

## [1.0.4] – 01.04.2025

#### Änderungen
- Die Verarbeitung eines Antrags erfolgt über die jeweilige Detailseite, was zuvor über die Tabelle erfolgte. Die Änderungen sorgen für deutlich mehr Komfort bei der Verarbeitung eines Antrags.
- Die Option ***Schlüsselrecht*** im Antragsformular für Eröffnungen und Mutationen wurde ergänzt. Neu kann hinterlegt werden, ob ein Badge oder Schlüssel benötigt wird.

## [1.0.3] – 25.03.2025

#### Änderungen
- Beim Anzeigen der Detailansicht eines Antrags wird der (bereits deaktivierte) Speichern-Button ausgeblendet, da dies für Verwirrung sorgte.
- Wenn ein Ticket nicht erstellt oder aktualisiert werden kann, wird die Anfrage geloggt. Details zum Fehler werden bereits geloggt.
- Wenn der Test-Modus aktiviert und der angemeldete Benutzer ein Admin ist, wird der Hinweis auf jeder Seite angezeigt.
- Das KIS Eintrittsdatum wird auf den Tag gesetzt, an dem der KIS-Benutzer über UMS erstellt wird (Export CSV) anstelle einen Tag vor dem Eintrittsdatum des Mitarbeiters.
- Der Button für das Versenden der Info-Mail bei Eröffnungen wird immer angezeigt, auch bei archivierten Einträgen. Der mehrfache Versand ist dadurch möglich. Die Empfänger können bearbeitet werden.

#### Bugfixes
- Wenn ein Eintritt oder eine Mutation in weniger als drei Wochen erfolgen soll, wird der Hinweis im Mail korrekt angezeigt.

## [1.0.2] – 24.03.2025

#### Änderungen
- Beim Erstellen des AD-Benutzers werden nicht mehr, wie bis anhin, Benutzername und E-Mail-Adresse geladen, die beim Erstellen des Antrags generiert wurden. Benutzername und E-Mail-Adresse werden nochmals neu generiert, falls Änderungen am Vor- oder Nachnamen durchgeführt wurden. Das Passwort des Benutzers wird in der Tabelle angezeigt.
- Im KIS Export wird ein statisches Austrittsdatum gesetzt (02.01.2100).
- Beim Laden des Modals für die Telefonie-Aktionen werden nun die gleichen Bezeichnungen wie im Antragsformular angezeigt.

## [1.0.1] – 22.03.2025

#### Neue Features
- Die Spalten in den Tabellen können nun via Drag and Drop sortiert werden. Die Reihenfolge wird im Local Storage des Browsers hinterlegt.
- Beim Scrollen in den Tabellen wird der Header fixiert und bleibt dadurch beim Scrollen sichtbar.

#### Änderungen
- Jeder Mailversand wird protokolliert.

## [1.0.0] – 21.03.2025

#### Bugfixes
- Beim Schliessen der Detailansicht von Anträgen über den Schliessen- bzw. Abbrechen-Button wird der Overlay-Hintergrund korrekt ausgeblendet.
