db.trajets_geolocalisation.drop();

db.createCollection("trajets_geolocalisation", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: ["covoiturage_id", "point_depart", "point_arrivee"],
            properties: {
                covoiturage_id: {
                    bsonType: "number",
                    description: "Lien vres la table MariaDB, Doit etre un nombre"
                },
                point_depart: {
                    bsonType: "Object",
                    required: ["type", "coordinates"],
                    properties: {
                        type: {
                            bsonType: "string",
                            enum: ["Point"],
                            description: "Doit etre 'Point'"
                        },
                        coordinates: {
                            bsonType: "Array",
                            minItems: 2,
                            maxItems: 2,
                            items: {
                                bsonType: ["double", "int", "long"],
                                description: "Coordonnees [longitude, latitude]"
                            }
                        }
                    }
                },
                point_arrivee: {
                    bsonType: "Object",
                    required: ["type", "coordinates"],
                    properties: {
                        type: {
                            bsonType: "string",
                            enum: ["Point"],
                            description: "Doit etre 'Point'"
                        },
                        coordinates: {
                            bsonType: "Array",
                            minItems: 2,
                            maxItems: 2,
                            items: {
                                bsonType: ["double", "int", "long"],
                                description: "Coordonnees [Longitude. Latitude]"
                            }
                        }
                    }
                },
                itineraire_complet: {
                    bsonType: "string",
                    description: "Polyligne encodee de l'itineraire"
                },
                distance_km: {
                    bsonType: "number",
                    description: "Duree estimee en minutes"
                },
                duree_min: {
                    bsonType: "number",
                    description: "Distance totale en km"
                },
                date_creation: {
                    bsonType: "date",
                    description: "Date de creation du covoiturage"
                }
            }
        }
    },
    validationLevel: "strict",
    validationAction: "error"
});

// Creation d'index pour les performances
db.trajets_geolocalisation.createIndex({"covoiturage_id": 1}, {unique: true});
db.trajets_geolocalisation.createIndex({"point_depart": "2dsphere"});
db.trajets_geolocalisation.createIndex({"point_arrivee": "2dsphere"});
db.trajets_geolocalisation.createIndex({"date_creation": -1});