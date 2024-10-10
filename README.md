# OpenAI Erweiterung für Contao CMS

Diese Erweiterung ermöglicht die Integration von OpenAI in das Contao CMS und ist mit Contao 4.13 und Contao 5 kompatibel. 
Mit dieser Erweiterung können Sie Dateien in den OpenAI Vektor Store hochladen, einen Assistenten erstellen und einen Chatbot für Ihre Website konfigurieren.

## Features

- **Upload von Dateien in den OpenAI Vektor Store**: Sie können verschiedene Dateien hochladen, die zur Verbesserung der KI-Modelle verwendet werden.
- **Erstellung eines OpenAI-Assistenten**: Sie können einen KI-Assistenten erstellen und ihn mit den hochgeladenen Vektor Store Dateien füttern, um präzisere Antworten zu erhalten.
- **Integration eines Chatbots**: Der Chatbot kann die Fragen der Website-Besucher beantworten, basierend auf den Informationen in den Vektor Store Dateien.
- **Kompatibilität mit Elasticsearch**: Die Erweiterung kann mit [Elasticsearch ProSearch Indexer Adapter Bundle](https://extensions.contao.org/?q=pro&pages=1&p=alnv%2Fprosearch-indexer-contao-adapter-bundle) integriert werden, um indexierte Seiten in den Vektor Store zu übertragen.

## Voraussetzungen

- **Contao CMS 4.13 oder Contao 5**
- **OpenAI-Lizenzschlüssel**: Eine gültige OpenAI-Lizenz ist erforderlich, um die Funktionen der Erweiterung nutzen zu können.

## Installation

1. **Erweiterung herunterladen und installieren**:
    - Die Erweiterung kann über den Contao Manager installiert werden.
    - Alternativ können Sie das Bundle manuell über Composer installieren:
      ```bash
      composer require alnv/contao-open-ai-assistant-bundle
      ```

2. **OpenAI-Lizenzschlüssel hinzufügen**:
    - Eine gültige OpenAI-Lizenz ist erforderlich.
    - Gehen Sie zu [OpenAI](https://platform.openai.com/signup) und erstellen Sie ein Konto.
    - Nach der Registrierung finden Sie den API-Schlüssel im Dashboard.
    - Speichern Sie den API-Schlüssel im Contao-Backend unter **Einstellungen → OpenAI-Einstellungen**.

## Anleitung zur Verwendung

### 1. Dateien in den OpenAI Vektor Store hochladen

Sie können Dateien (z.B. PDF, Textdateien) im Backend in den OpenAI Vektor Store hochladen. Dies hilft, relevante Informationen für Ihren Assistenten zu speichern.

### 2. Erstellen eines OpenAI-Assistenten

Ein Assistent kann mit den hochgeladenen Vektor Store Dateien trainiert werden. Der Assistent nutzt diese Daten, um Anfragen besser zu beantworten.

**Hinweise für einen guten Prompt**:

- Ein guter Prompt ist entscheidend für die Leistung und Genauigkeit des Assistenten.
- Formulieren Sie Ihren Prompt klar und präzise.
- Geben Sie dem Assistenten ausreichend Kontextinformationen.
- Beispiel für einen effektiven Prompt: *"Du bist ein Kundenservice-Assistent für eine Online-Buchhandlung. Antworte höflich und präzise auf Anfragen zu Büchern, Lieferungen und Rücksendungen."*

**Warum ist ein guter Prompt wichtig?**

Ein präziser und gut durchdachter Prompt hilft dem KI-Modell, die Anfrage besser zu verstehen und eine relevante Antwort zu liefern. Er definiert den Ton und den Kontext der Konversation und beeinflusst somit direkt die Qualität der Antworten.

### 3. Chatbot erstellen

Nachdem der Assistent erstellt wurde, können Sie einen Chatbot im Backend hinzufügen (Frontend-Modul). Der Chatbot kann dann in die Website integriert werden, um die Fragen der Besucher basierend auf den Informationen im Vektor Store zu beantworten.

## Integration mit Elasticsearch

Diese Erweiterung unterstützt auch die Integration mit Elasticsearch. Um diese Funktion zu nutzen, müssen Sie die [Elasticsearch Erweiterung](https://extensions.contao.org/?q=pro&pages=1&p=alnv%2Fprosearch-indexer-contao-adapter-bundle) installieren.

### Anleitung zur Elasticsearch-Integration

1. **Elasticsearch Erweiterung installieren**:
    - Installieren Sie das `alnv/prosearch-indexer-contao-adapter-bundle` über den Contao Manager oder Composer.

2. **Suche und Index aufbauen**:
    - Gehen Sie im Backend zu **Elasticsearch → Suche** und erstellen Sie eine neue Suche.
    - Gehen Sie zu **Elasticsearch → Indexes** und legen Sie einen Index an.

3. **Vektordatei erstellen**:
    - Gehen Sie zu **Elasticsearch → Indexes** und klicken Sie oben rechts auf den Menüpunkt **"Vektordatei erstellen"**.
    - Wählen Sie die zu indexierenden Inhalte aus und erstellen Sie eine Vektordatei.

4. **Vector Store aktualisieren**:
    - Nachdem die Vektordatei erstellt wurde, klicken Sie auf **"Speichern und Schließen"**.
    - Klicken Sie anschließend auf **"Vector Store aktualisieren"**, um die Dateien automatisch in den OpenAI Vektor Store hochzuladen.

### Verwendung des Vektor Store für den Assistenten

- Sobald die Vektor Store Dateien hochgeladen sind, können Sie diese für Ihren Assistenten verwenden, um die Qualität der Antworten weiter zu verbessern.

## Support

Bei Fragen oder Problemen können Sie sich gerne an das Entwicklerteam wenden.

Viel Erfolg mit der OpenAI-Integration in Contao!