# Next release

# 4.2.0
- CUS-225 - Behebt ein Problem, bei dem Ausgeschlossene Kombinationen nicht korrekt in der Storefront validiert wurden
- CUS-539 - Korrigiert die Berechnung des Einzelpreises für Customized Products Positionen
- CUS-648 - Behebt ein Problem, bei dem der Rabatt nur einmal gewährt wurde
- NEXT-29020 – Integration der Vue3-Kompatibilität in SwagCustomizedProducts

# 4.1.0
- CUS-543 - Migration von Custom Products

# 4.0.2
- CUS-619 - Funktion "Selbst-schließende Optionen" für Select-Optionstypen korrigiert
- CUS-622 - Fehlende icons in der Administration wieder hinzugefügt

# 4.0.1
- CUS-607 - Darstellungsfehler in Schritt-für-Schritt Modus korrigiert

# 4.0.0
- CUS-562 - Kompatibilität zur Shopware Version 6.5.0.0 sichergestellt

# 3.4.5
- CUS-540 - Behebt ein Problem, bei dem Preise in PDP-Layouts falsch umgebrochen werden
- CUS-283 - Behebt ein missverständliches Design bei der Erstellung von ausgeschlossenen Kombinationen im Admin-Bereich
- CUS-545 - Verbessert SEO und behebt ein Problem bei mobilem Styling für Preise

# 3.4.4
- CUS-538 - Behebt ein Problem, bei dem zu viele Elemente geladen wurden

# 3.4.3
- CUS-526 - Behebt ein Problem, bei dem eine Migration erhöhte Berechtigungen erforderte

# 3.4.2
- CUS-278 - Behebt ein Problem, bei dem Rabatte für relative Zuschläge falsch berechnet wurden.
- CUS-279 - Behebt ein Problem, bei dem Rabatte bei Verwendung bestimmter Regeln falsch angewendet wurde.
- CUS-514 - Die Ladezeiten der Produktdetailseite wurde verbessert, indem die Anzahl der Datenbankaufrufe reduziert wurde. Eine deutliche Verbesserung ist vor allem bei der Option "Bildauswahl" zu verzeichnen. ([Stefan Poensgen](https://github.com/stefanpoensgen))

# 3.4.1
- CUS-268 - Behebt ein Problem, bei dem Rabatte verdoppelt wurden, wenn sich ein Custom Product im Warenkorb befand

# 3.4.0
- CUS-213 - Behebt ein Problem beim Nutzen von Rabatten mit Custom Products
- CUS-251 - Behebt ein Problem, bei dem in den Bestelldetails eine fehlerhafte Warnung über gelöschte Produkte angezeigt wurde

# 3.3.0
- CUS-207 - Der Bildupload erlaubt nun die Exklusion von definierten Dateitypen
- CUS-228 - Es wird keine ID mehr im Warenkorb angezeigt bei Produkten mit Auswahlfeldern
- DYN-18 - Die Preisbox berücksichtigt jetzt Warenkorbregeln

# 3.2.0
- CUS-216 - Nutzen der neuen Ansicht von verschachtelten Bestellpositionen in der Storefront und Admin
- CUS-217 - In Rechnungen, Lieferbestätigungen und Storno-Dokumenten wird eingegebener Text wieder korrekt angezeigt

# 3.1.1
- CUS-144 - Behebt ein Problem, bei dem Standardwerte von Mehrfachauswahlen nicht korrekt geteilt oder editiert werden konnten
- CUS-197 - Fügt die Unterstützung von verschachtelten Bestellpositionen auf der Bestellungsdetailseite hinzu
- CUS-202 - In Rechnungen, Lieferbestätigungen und Storno-Dokumenten wird der Dateiname von hochgeladenen Dateien angezeigt
- CUS-203 - Der Dateiupload berücksichtig jetzt auch die maximale Dateizahl beim Hinzufügen via drag and drop
- CUS-209 - Fügt Events zum Datei- & Bildupload hinzu, die bei Erfolg, Fehler oder Entfernen des Uploads geworfen werden
- CUS-209 - Aktiviert & deaktiviert den Weiter-Button nun korrekt, wenn der Datei- oder Bildupload valide ist
- CUS-212 - Aktiviert & deaktiviert den Weiter-Button nun korrekt, wenn das Datums- oder Zeitfeld valide ist
- CUS-219 - Plugin-Leistung verbessert

# 3.1.0
- CUS-146 - Der "Nächster Schritt" Button im Schritt-für-Schritt-Modus ist bei den erforderlichen Optionen standardmäßig deaktiviert
- CUS-152 - Deaktiviert initial den Button "Produkt konfigurieren", um Probleme beim Rendern des Konfigurationscontainers beim Laden der Produkt Detail Seite zu vermeiden
- CUS-188 - Behebt ein Problem bei den Emails
- CUS-193 - Behebt die Anzeige von Einmalaufschlägen in E-Mails und Dokumenten
- CUS-194 - Ältere Versionen von Templates werden nun im Hintergrund aufgeräumt
- CUS-200 - Fügt die Unterstützung von `*.bmp`, `*.eps`, `*.svg`, `*.tif` & `*.webp` Dateitypen in der Upload-Komponente der Storefront hinzu

# 3.0.0
- CUS-134 - Merkzettel für Custom Products implementiert
- CUS-158 - Kompatibilität für Shopware 6.4
- CUS-165 - Verschieben der Zuweisungskarte der Custom Products in den Spezifikationen-Tab
- CUS-183 - Custom Products für CMS-Produkt-Detailseiten & CMS-Kaufelemente implementiert

# 2.11.2
- CUS-177 - Korrigiert die Werte von Variantenprodukten in Dokumenten und entfernt das Abschneiden von Produktnummern

# 2.11.1
- CUS-180 - Behebt ein Problem durch welches das Plugin fälschlicherweise mit Shopware 6.4 kompatibel markiert war

# 2.11.0
- CUS-23 - Funktionalität zum leeren folgender Optionstypen implementiert: `Textfeld`, `Datumsfeld` und `Zeitauswahlfeld`
- CUS-34 - Bestellbestätigung stellt Optionen nun in korrekter Reihenfolge dar
- CUS-102 - Der angegebene Wert des Nutzers wird nun für Optionen in Rechnungen und Lieferscheinen angezeigt
- CUS-116 - Maximale Länge des Konfigurationstextes im Checkout verbessert
- CUS-127 - Aktualisierung der Preisbox bei Änderungen der Menge korrigiert
- CUS-133 - "In den Warenkorb"-Button deaktiviert, wenn erforderliche Optionsfelder in der Storefront nicht ausgewählt sind
- CUS-135 - Korrigiert falsche Anzeige von Zuschlägen mit gleicher Bezeichnung in der Storefront 
- CUS-137 - Korrigiert die Grundpreisdarstellung für Nicht-Custom-Products-Positionen
- CUS-145 - Laden von Medien auf der Detailseite des Admin-Moduls verbessert
- CUS-147 - Korrigiert die Bearbeitung von regulären Bestellpositionen während das Customized Product Plugin installiert ist
- CUS-155 - Behebt ein Problem, bei dem Duplizieren von Templates mit Ausschlüssen
- CUS-157 - Step-by-Step-Modus startet von vorne, sobald die Seite neu geladen wird
- CUS-160 - Der angegebene Wert des Nutzers wird nun für Optionen in der Bestellbestätigung angezeigt

# 2.10.0
- CUS-150 - Routen für die Store-API hinzugefügt und Kompatibilität mit Shopware 6.3.5.1 sichergestellt

# 2.9.1
- CUS-150 - Kompatibilität zum Wunschlisten-Feature verbessert

# 2.9.0
- CUS-107 - Behebt ein Problem, bei dem Daten für Custom Products in der Produkttabelle bestehen bleiben
- CUS-136 - Produkte können nicht mehr direkt zum Warenkorb hinzugefügt werden, wenn sie eine aktive Produktvorlage zugewiesen bekommen haben

# 2.8.0
- CUS-46 - Für die Farb-, Bild- und Textauswahl kann nun ein Standardwert ausgewählt werden
- CUS-63 - Verhindert das Neubestellen von Produkten, die in der Zwischenzeit zu Custom Products wurden
- CUS-83 - Fehlermeldungen für Zahlenfelder hinzugefügt und Standardwert ist nun optional
- CUS-93 - Nachricht zum Anzeigen von leeren Produkt-Konfigurationen hinzugefügt
- CUS-95 - Suchen nach Optionen hat keinen Effekt auf die Ausschluss-Auflisting mehr
- CUS-97 - Mehrfachvererbung der Positionsübersicht im Bestellungsmodul korrigiert
- CUS-99 - Korrigiert Installation bei aktivierter Primärschlüssel-Anforderung der Datenbank
- CUS-108 - Behebt Installation bei Systemen ohne englische Sprache
- CUS-111 - Datumsfelder funktionieren nun als Pflichtoption
- CUS-112 - Korrigiert inkorrekte Fehlermeldungen beim Datei- und Bildupload
- PPI-174 - Warenkorb- und Bestellpositionen werden jetzt korrekt zu PayPal übertragen

# 2.7.0
- CUS-35 - Platzhalter für Custom Products Options können nun übersetzt werden. Fallbacksprache für Auswahlfeld-Werte korrigiert
- CUS-38 - Fehlermeldungen zu Datei-Uploads hinzugefügt und der Kaufen-Button ist nun deaktiviert, wenn zu viele Dateien hochgeladen wurden
- CUS-81 - ACL-Privilegien zum Custom-Products-Modul hinzugefügt
- CUS-84 - Felder der Eigenschaften der Custom Product Options standardisiert
- CUS-86 - Datenbankmigrationen korrigiert

# 2.6.5
- CUS-86 - Datenbankmigrationen korrigiert

# 2.6.4
- CUS-16 - Korrigiert ein Speicherproblem, das auftritt, wenn eine Custom Products Option bearbeitet werden sollte, diese aber zuvor ohne Konfiguration angelegt war
- CUS-55 - Korrigiert das Löschen von Custom Product Vorlagen
- CUS-60 - Korrigiert die Positionierung und das Tabellenverhalten der Optionstypen im Custom-Products-Modul
- CUS-61 - Darstellung der Variantenspezifikationen im Warenkorb hinzugefügt, wenn ein Custom-Products-Template angewendet wurde
- CUS-62 - Verhalten der Optionen verändert, so dass diese auf einen Tastendruck und nicht auf eine Änderung reagieren
- CUS-78 - Titel der Benachrichtigungen an Shopware-Standard angepasst

# 2.6.3
- CUS-19 - Korrigiert die Übersetzung der Preisbox bei mehrsprachigen Saleschannels
- CUS-57 - Fügt die Variantenspezifikationen in der Bestellübersicht für Custom Products hinzu
- CUS-66 - Korrigiert das Schreiben von Produkten ohne Custom Products Daten

# 2.6.2
- CUS-32 - Verbessern der Validierung beim Aktualisieren der Optionen
- CUS-49 - Produktnummern werden bei Produkten korrekt angezeigt
- CUS-50 - Korrigiert den Link-Titel von Positionen im Warenkorb
- CUS-58 - Korrigiert das Laden von Custom Products auf der Produktdetailseite

# 2.6.1
- CUS-3 - Die Position eines Rahmens im Kontobereich wurde angepasst

# 2.6.0
- CUS-6 - Es wurde eine Einstellung hinzugefügt, welche den Kunden auffordert seine Angaben zu bestätigen

# 2.5.1
- CUS-29 - Custom Products kann jetzt für verschiedene Standardsprachen installiert werden

# 2.5.0
- CUS-1 - Upload-Optionstypen können jetzt mit Auschlüssen verwendet werden
- CUS-5 - Drittentwickler-WYSIWYG-Editor durch eigene Lösung ersetzt
- CUS-10 - Medien- und Fehlerverwaltung durch Shopwares Best Practices verbessert
- CUS-12 - Kunden können ihre Konfiguration nun von der Produktdetailseite aus teilen und durch den Warenkorb-Prozess hinweg bearbeiten
- CUS-14 - HTML-Editor Formatierung wird nun in der Vorschau im Warenkorb angezeigt
- CUS-15 - Der Kauf von Produkten mit einer leeren Vorlage für Custom Products ist jetzt möglich
- CUS-20 - Entfernt die Möglichkeit mit der Tastatur im Schritt-für-Schritt-Modus von einer Seite zur anderen zu springen
- CUS-22 - Test E-Mails können jetzt aus der Administration versendet werden, wärend Custom Products installiert ist
- CUS-24 - Rahmen um Farbselektion für eine bessere Sichtbarkeit hinzugefügt

# 2.4.0
- PT-11483 - Zeitraumbeschränkung des Zeitauswahlfeldes behoben
- PT-11563 - Sortieren der Optionswerte ist jetzt möglich
- PT-11912 - Die Storefront Übersetzungen werden jetzt automatisch registriert
- PT-11918 - Shopware 6.3 Kompatibilität
- PT-11950 - Der Optionswerte-Baum kann jetzt gescrollt werden
- PT-11954 - Datum- / Zeitfelder zeigen nach dem Speichern wieder den richtigen Wert an

# 2.3.0
- PT-11308 - Nummernfeld-Konfiguration für "Nachkommastellen" entfernt, welche im Widerspruch zur "Schrittweiten"-Konfiguration steht
- PT-11310 - Zeit- und Datumsvalidierung für die Optionstypen Datumsfeld und Zeitauswahlfeld eingebaut 
- PT-11370 - In der Storefront beim Klick auf ein Bild in der Bildauswahl wird dieses in einer Vollbild-Ansicht dargestellt
- PT-11452 - Optionales Einklappen von Optionen implemeniert, wenn eine neue angeklickt wird und die vorherige valide ist
- PT-11621 - Fehlgeschlagene Bestellungen welche ein oder mehrere Custom Products enthalten können jetzt im Account editiert werden
- PT-11719 - Duplizieren der Produktvorlage deaktiviert, wenn diese noch nicht gespeichert wurde.
- PT-11775 - Erforderliche Optionen vom Typ Datei- und Bildupload blockieren nicht länger die Bestellung eines Custom Product
- PT-11823 - Der HTML-Editor wird nun im Schritt-für-Schritt-Modus vertikal richtig ausgerichtet
- PT-11840 - Der Button "Produkt hinzufügen" ist im Bestellmodul wieder verfügbar
- PT-11881 - Kompatibilität für Safari und Internet Explorer 11
- PT-11897 - Datei- und Bildupload geben nun korrekte Fehler aus, wenn die maximale Anzahl oder Dateigröße beim Upload überschritten wurden

# 2.2.0
- PT-11172 - Custom-Products-Optionsmodal auf feste Größe angepasst
- PT-11288 - Relative Zuschläge werden jetzt korrekt in der Auftragsbestätigungsmail aufgeführt
- PT-11303 - Der kalkuierte Preis eines Custom Products, samt Auflistung der einzelnen Optionen, wird jetzt überhalb des Warenkorb-Buttons aufgeschlüsselt
- PT-11312 - Erforderliche Optionen des Custom Product werden automatisch auf der Produktdetailseite ausgeklappt
- PT-11359 - Prozentuale Einmalaufschläge werden jetzt korrekt berechnet
- PT-11466 - Gewährleistet die Kompatibilität zwischen Promotionen und Customized Products
- PT-11632 - Die Navigation für Elemente, die wegen eines Ausschlusses nicht kombiniert werden können, wurde hinzugefügt
- PT-11773 - Custom Products mit nicht gefüllten erforderlichen Optionen können nicht mehr in den Warenkorb gelegt werden
- PT-11774 - Der Step-by-Step-Modus schneidet die Konfiguration für Bild- und Datei-Uploads nicht mehr ab
- PT-11868 - Multiselect-Validierung für Step-By-Step-Modus implementiert

# 2.1.0
- PT-11476 - Stellt Store-API-Endpunkte zur Verfügung
- PT-11799 - Kunden ohne Bestellung können die Accountübersicht wieder aufrufen

# 2.0.0
- PT-11724 - Löscht die Storefront-Uploads beim deinstallieren
- PT-11743 - Stellt die Erweiterbarkeit des SalesChannelProductCriteria sicher

# 1.3.3
- PT-11698 - Maximal dargestellte Options-Auswahlmöglichkeiten erhöht
- PT-11738 - Behebt ein Problem mit den Kunden Uploads
- PT-11739 - Behebt die Plugin-Installation für Shops, in denen die Sprachen Deutsch und/oder Englisch fehlen

# 1.3.2
- PT-11427 - Aufschlagsdarstellung bei Template Optionen und dessen Auswahlmöglichkeiten in Nicht-Standard-Währungen korrigiert
- PT-11587 - Improve document creation
- PT-11701 - Korrigiert die Template-Duplizierung

# 1.3.1
- PT-11652 - Behebt einen Fehler, bei dem der Upload-Endpunkt falsch bestimmt wurde
- PT-11651 - Behebt einen Fehler, bei dem bestimmte Dateinamen nicht hochgeladen werden konnten
- PT-11164 - Behebt einen Fehler, der verhindert dass die gleiche Datei mehrfach hochgeladen werden kann

# 1.3.0
- PT-11607 - Shopware 6.2 Kompatibilität
- PT-11474 - Duplizieren von Custom Products
- PT-11426 - Ausschlüsse implementiert
- PT-10937 - Datei- und Bildupload implementiert

# 1.2.1
- NTR - Löst ein Problem, bei dem die Bestellübersicht nicht richtig funktioniert hat

# 1.2.0
- PT-10906 - Step-By-Step Modus hinzugefügt
- PT-11306 - Fügt eine "Wert(e)"-Spalte hinzu, welches die Anzahl der Options Werte ausgibt
- PT-11309 - Die Benennung der "Bestellnummer" wurde in "Options-Produktnummer" innerhalb der Optionen geändert
- PT-11314 - Verbessert die Kompatibilität mit der QuickView des CMS Extensions Plug-ins
- PT-11316 - Informationsbox zur Zuweisung hinzugefügt
- PT-11355 - Zeilenumbrüche werden jetzt im Warenkorb angezeigt
- PT-11422 - HTML-Editor Option hinzugefügt
- PT-11441 - Korrigiert die deutsche Übersetzung vom Beschreibungsfeld
- PT-11454 - Korrigiert Einrückung von Auswahl Optionen
- PT-11482 - Placeholder Feld bei der Nummernfeld Option entfernt
- PT-11496 - Preise werden nun nur neben einen Wert angezeigt wenn es sich um eine Selektion handelt
- PT-11554 - HTML-Editor-Einstellungsmöglichkeiten korrigiert

# 1.1.0
- PT-10720 - Fehlerbehandlung im Option-Modal verbessert
- PT-11110 - Erweiterung der Dokumente optimiert
- PT-11226 - Link zur Produkt-Detailseite aus dem Bestellmodul korrigiert
- PT-11227 - Aufschlagswerte wurden zur Custom-Products-Konfiguration hinzugefügt und der Preis im Position listing wurde angepasst
- PT-11236 - Löst ein Problem mit der Validierung der Farbauswahl
- PT-11249 - Löst ein Problem, bei dem keine Preisregeln hinzugefügt werden konnten
- PT-11250 - Löst ein Problem mit ungültigen Auswahloptionen
- PT-11253 - Anzeige von Toggle-Switches anstatt Checkboxes
- PT-11255 - Pflichtfelder der Auswahloptionen in der Storefront korrigiert
- PT-11278 - Storefront-Stylefixes für Aufschläge wurden angewandt
- PT-11279 - Erweiterung des Bestellmoduls optimiert
- PT-11280 - Fügt das neue Warenkorb-Layout zur Bestellhistorie hinzu
- PT-11282 - Ausblenden der Listenpreise im Option-Modal
- PT-11286 - Korrigiert Bearbeitung der Namen von Unterelementen. Button zum Hinzufügen eines Unterelements hinzugefügt
- PT-11289 - Die Beschreibung für Optionstypen wird jetzt in der Storefront angezeigt
- PT-11290 - Löst ein Problem mit der Anzeige des Zuschlags im Warenkorb
- PT-11302 - Erweitert die Optionstypen um die Bildauswahl
- PT-11307 - Die nicht benötigte Pflichtoption-Einstellung wurde aus dem Checkbox-Optionstyp entfernt
- PT-11311 - Erweiterungs- und Verkleinerungsfunktion zu den Textoptionen im Warenkorb hinzugefügt
- PT-11315 - Platzhalter optimiert
- PT-11352 - Renderer für Imageselect in der Bestellübersicht implementiert
- PT-11362 - Optionsaufschläge werden nun Anhand der tatsächlichen Menge 1 berechnet

# 1.0.0
- PT-11144 - Löst ein Problem, bei dem Custom Products nicht mit Produktvarianten funktioniert hat
- PT-11145 - Löst ein Problem, bei dem erforderliche Optionen nicht validiert wurden
- PT-11149 - Ermöglicht die Nachbestellung von Custom Products im Shop-Konto
- PT-11150 - Einführung der Fehlerbehandlung in der Administration
- PT-11151 - Löst ein Problem, bei dem der Platzhalter der Optionsliste verschwindet
- PT-11154 - Löst ein Problem, bei dem gleich konfigurierte Produkte nicht gruppiert wurden
- PT-11162 - Fügt das neue Warenkorb-Layout hinzu
- PT-11180 - Löst ein Problem, bei dem Custom Products gekauft werden konnten, ohne sie zu konfigurieren
- PT-11198 - Löst ein Problem bei der Erstellung von Bestelldokumenten
- PT-11218 - Verbessert die Verwaltung der Übersetzungen von Optionen
- PT-11219, PT-11208, PT-11159 - Anzeige-Optimierungen in der Storefront
- PT-11220 - Löst ein Problem mit der Auftragsbestätigungsmail
- PT-11236 - Optimiert die Handhabung von Optionen in der Storefront

# 0.9.0
- Erste Veröffentlichung von Custom Products für Shopware 6
