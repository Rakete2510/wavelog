# Two-Factor Authentication (2FA) Integration für Wavelog

Diese Implementierung fügt TOTP-basierte Zwei-Faktor-Authentifizierung zu Wavelog hinzu.

## Funktionen

- **TOTP-Unterstützung**: Kompatibel mit Google Authenticator, Authy, Microsoft Authenticator und anderen TOTP-Apps
- **Backup-Codes**: 10 einmalige Backup-Codes für den Fall, dass das Gerät verloren geht
- **Benutzerfreundlich**: Einfache Einrichtung über QR-Code
- **Sicher**: Verwendung von SHA-1 HMAC mit 30-Sekunden-Zeitfenstern
- **Rückwärtskompatibel**: OTP ist optional und nur für Benutzer aktiviert, die es einrichten

## Installierte Komponenten

### 1. Datenbank-Migration
- **Datei**: `application/migrations/248_add_otp_2fa_to_users.php`
- **Felder**:
  - `otp_secret`: Base32-kodierter geheimer Schlüssel
  - `otp_enabled`: Boolean-Flag für aktivierte 2FA
  - `otp_backup_codes`: JSON-Array von gehashten Backup-Codes
  - `otp_last_used`: Zeitstempel der letzten OTP-Verwendung

### 2. TOTP-Bibliothek
- **Datei**: `application/libraries/Totp.php`
- **Funktionen**:
  - Geheimschlüssel-Generierung
  - TOTP-Code-Generierung und -Verifizierung
  - QR-Code-URL-Generierung
  - Backup-Code-Management

### 3. Erweiterte User-Model
- **Datei**: `application/models/User_model.php`
- **Neue Methoden**:
  - `generateOtpSecret()`
  - `enableOtp()` / `disableOtp()`
  - `verifyOtp()`
  - `getOtpQrUrl()`
  - `regenerateBackupCodes()`

### 4. Controller-Erweiterungen
- **Datei**: `application/controllers/User.php`
- **Neue Endpoints**:
  - `/user/setup_otp` - OTP-Einrichtung
  - `/user/enable_otp` - OTP aktivieren
  - `/user/disable_otp` - OTP deaktivieren
  - `/user/verify_otp` - OTP-Verifizierung beim Login
  - `/user/regenerate_backup_codes` - Neue Backup-Codes
  - `/user/get_otp_qr` - AJAX QR-Code-Abruf

### 5. Benutzeroberflächen
- **Login-Formular**: `application/views/user/login.php`
  - Dynamisches OTP-Feld
  - Automatische Code-Formatierung
  - Auto-Submit bei 6-stelligen Codes

- **OTP-Setup**: `application/views/user/otp_setup.php`
  - QR-Code-Anzeige
  - Manueller Schlüssel-Fallback
  - Setup-Verifizierung

- **OTP-Verwaltung**: `application/views/user/otp_manage.php`
  - Status-Anzeige
  - Backup-Codes anzeigen/regenerieren
  - 2FA deaktivieren

- **OTP-Verifizierung**: `application/views/user/verify_otp.php`
  - Separates Login-Interface für OTP-Eingabe

## Verwendung

### Für Benutzer:

1. **Einrichtung**:
   - Gehe zu "Profil" → "Two-Factor Authentication"
   - Installiere eine Authenticator-App
   - Scanne den QR-Code oder gib den manuellen Schlüssel ein
   - Verifiziere die Einrichtung mit einem 6-stelligen Code

2. **Login mit 2FA**:
   - Gib Benutzername und Passwort ein
   - Das OTP-Feld erscheint automatisch
   - Gib den 6-stelligen Code aus der App ein
   - Oder verwende einen 8-stelligen Backup-Code

3. **Backup-Codes**:
   - Werden bei der Einrichtung angezeigt
   - Können in der 2FA-Verwaltung regeneriert werden
   - Jeder Code kann nur einmal verwendet werden

### Für Administratoren:

- Benutzer können selbständig 2FA aktivieren/deaktivieren
- Keine speziellen Admin-Berechtigungen erforderlich
- Gesperrte Accounts können über das normale User-Management entsperrt werden

## Sicherheitsmerkmale

- **Zeitbasierte Codes**: 30-Sekunden-Fenster mit ±1 Periode Toleranz
- **Replay-Schutz**: Verhindert Wiederverwendung derselben Codes
- **Sichere Backup-Codes**: Gehashed mit `password_hash()`
- **Geschützte Geheimnisse**: Base32-kodierte 256-Bit-Schlüssel
- **Fehlschlag-Zähler**: Integration in das bestehende Login-Attempt-System

## Technische Details

- **TOTP-Standard**: RFC 6238 konform
- **Hash-Algorithmus**: SHA-1 (Standard für Authenticator-Apps)
- **Code-Länge**: 6 Ziffern
- **Zeitfenster**: 30 Sekunden
- **Backup-Code-Format**: XXXX-XXXX (8 Zeichen)

## Kompatibilität

Getestet mit:
- Google Authenticator (Android/iOS)
- Microsoft Authenticator (Android/iOS)
- Authy (Android/iOS/Desktop)
- 1Password (Android/iOS/Desktop)

## Migration und Updates

Die Migration wird automatisch beim ersten Login nach dem Update ausgeführt. Bestehende Benutzer sind nicht betroffen und können 2FA optional aktivieren.

## Troubleshooting

### Häufige Probleme:

1. **"Invalid authentication code"**:
   - Überprüfe die Systemzeit auf Server und Gerät
   - Stelle sicher, dass der Code aktuell ist (nicht abgelaufen)

2. **QR-Code lädt nicht**:
   - Überprüfe die Internetverbindung (verwendet qr-server.com)
   - Verwende den manuellen Schlüssel als Fallback

3. **Gerät verloren**:
   - Verwende einen Backup-Code zum Login
   - Deaktiviere 2FA und richte es mit dem neuen Gerät ein
