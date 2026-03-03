import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
import pickle

# 1. Génération de données synthétiques LOGIQUES (1000 étudiants)
print("1. Génération des données d'étudiants...")
np.random.seed(42)
n_samples = 1000

# Features
# - certifs : de 0 à 10
# - niveau : 0 (Débutant), 1 (Intermédiaire), 2 (Avancé)
# - progression : de 0 à 100
# - cat_match : 0 (Non), 1 (Oui) - Nouveau !
certifs = np.random.randint(0, 11, n_samples)
niveau = np.random.randint(0, 3, n_samples)
progression = np.random.randint(0, 101, n_samples)
cat_match = np.random.randint(0, 2, n_samples)

df = pd.DataFrame({
    'certifs': certifs,
    'niveau': niveau,
    'progression': progression,
    'cat_match': cat_match
})

# 2. Règle Logique pour le Label "A_Reussi" (0 ou 1)
def determine_success(row):
    # On rend le modèle plus réaliste et moins "généreux"
    # - La progression compte moins (max 60 pts au lieu de 120)
    # - Le cat_match est important pour atteindre le sommet (30 pts)
    # - Le seuil est relevé à 50 pour éviter le 100% automatique
    
    score = (row['certifs'] * 3) + (row['niveau'] * 8) + (row['progression'] * 0.6) + (row['cat_match'] * 30)
    
    # Ajout d'un peu d'aléatoire pour varier les probabilités (pas de 0% ou 100% systématique)
    bruit = np.random.normal(0, 10)
    score += bruit
    
    # Seuil de réussite à 55. 
    # Même avec 100% de progression (60 pts), sans cat_match et sans certifs, on est proche du seuil.
    if score > 55:
        return 1
    else:
        return 0

df['reussite'] = df.apply(determine_success, axis=1)

print(f"Bilan de réussite généré : {df['reussite'].value_counts().to_dict()}")

# 3. Entraînement du Modèle d'Intelligence Artificielle
print("2. Entraînement de l'Intelligence Artificielle (Random Forest)...")
X = df[['certifs', 'niveau', 'progression', 'cat_match']]
y = df['reussite']

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

# Précision basique
accuracy = model.score(X_test, y_test)
print(f"Modèle entraîné avec une précision de : {accuracy * 100:.2f}%")

# 4. Sauvegarde du Cerveau de l'IA (Dans le même dossier que le script)
import os
model_dir = os.path.dirname(__file__)
model_path = os.path.join(model_dir, 'model.pkl')

with open(model_path, 'wb') as f:
    pickle.dump(model, f)
    
print(f"3. Cerveau de l'IA ({model_path}) généré avec succès !")
