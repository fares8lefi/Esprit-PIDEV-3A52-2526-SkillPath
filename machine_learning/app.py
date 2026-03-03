from flask import Flask, request, jsonify
import pickle
import numpy as np
import os

app = Flask(__name__)

# Chargement du modèle au démarrage du serveur
model_path = os.path.join(os.path.dirname(__file__), 'model.pkl')

try:
    with open(model_path, 'rb') as f:
        model = pickle.load(f)
    print("Modèle IA chargé avec succès.")
except FileNotFoundError:
    print("ATTENTION : Le fichier model.pkl est introuvable. Veuillez exécuter train_model.py d'abord.")
    model = None

@app.route('/api/predict', methods=['POST'])
def predict():
    if model is None:
        return jsonify({'error': 'Modèle IA non disponible.'}), 500

    try:
        # L'API Symfony de AIService envoie : {"model": "...", "features": [val1, val2, val3]}
        data = request.get_json()
        features = data.get('features', [])
        
        if len(features) != 4:
            return jsonify({'error': 'Précisément 4 features sont requises (certifs, niveau, progression, cat_match).'}), 400

        # On transforme le tableau pour l'IA
        print(f"DEBUG IA - Features reçues : {features}")
        features_array = np.array([features])
        
        # Prédiction des probabilités
        # proba[0][0] = Probabilité d'échec
        # proba[0][1] = Probabilité de succès
        prediction_proba = model.predict_proba(features_array)
        prob_success = float(prediction_proba[0][1])

        return jsonify({
            'prediction': {
                'probabilities': [1 - prob_success, prob_success] # Symfon attend l'index 1
            }
        })

    except Exception as e:
        return jsonify({'error': str(e)}), 400

if __name__ == '__main__':
    # Lance le serveur sur le port 5000
    app.run(host='0.0.0.0', port=5000, debug=True)
