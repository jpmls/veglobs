{% extends 'base.html.twig' %}

{% block title %}{{ title }} - VeGlobs{% endblock %}

{% block body %}
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="{{ path('app_news') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
                <h1 class="h4 mb-0">{{ title }}</h1>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Titre</label>
                            <input type="text" name="title" class="form-control"
                                value="{{ news ? news.title : '' }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Contenu</label>
                            <textarea name="content" class="form-control" rows="6" required>{{ news ? news.content : '' }}</textarea>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Réseau</label>
                                <select name="network" class="form-select">
                                    {% for val in ['metro', 'rer', 'bus', 'tram'] %}
                                        <option value="{{ val }}" {{ news and news.network == val ? 'selected' : '' }}>
                                            {{ val|upper }}
                                        </option>
                                    {% endfor %}
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ligne</label>
                                <input type="text" name="line" class="form-control"
                                    value="{{ news ? news.line : '' }}" placeholder="Ex: 4, A, 174...">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Type</label>
                                <select name="type" class="form-select">
                                    {% for val in ['perturbation', 'travaux', 'incident', 'info'] %}
                                        <option value="{{ val }}" {{ news and news.type == val ? 'selected' : '' }}>
                                            {{ val|capitalize }}
                                        </option>
                                    {% endfor %}
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Source</label>
                                <select name="source" class="form-select">
                                    <option value="official" {{ news and news.source == 'official' ? 'selected' : '' }}>Officielle</option>
                                    <option value="community" {{ news and news.source == 'community' ? 'selected' : '' }}>Communauté</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                {{ news ? 'Enregistrer' : 'Créer' }}
                            </button>
                            <a href="{{ path('app_news') }}" class="btn btn-outline-secondary">Annuler</a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
{% endblock %}
