export default class ChangesetHelper {

    constructor() {
        this.ignoredPropertyPatterns = [
            new RegExp(/^extensions.*/),
            new RegExp(/^.*.slots.[0-9]+.data.*/),
            new RegExp(/^.*.config.product.entity.*/),
            new RegExp(/^.*.cover.media.*/),
            new RegExp(/^.*.context.*/),
            new RegExp(/.locked$/),
        ]

        this.newPropRegexp = new RegExp(/._isNew$/);
        this.idRegexp = new RegExp(/.id$/);
    }

    hasUnsavedChanges(entity, origin) {
        const originPaths = this.getJoinedPaths(origin);

        const deletions = this.getDeletions(originPaths, entity);

        if (!!deletions.length) {
            return true;
        }

        const additions = this.getAdditions(this.getJoinedPaths(entity));

        if (!!additions.length) {
            return true;
        }

        const changes = this.getChanges(originPaths, origin, entity);

        return !!changes.length;
    }

    getDeletions(originPaths, entity) {
        return this.getSpecificPaths(originPaths, this.idRegexp).filter((idPath) => {
            if (!this.getProperty(entity, idPath)) {
                return true;
            }
        });
    }

    getAdditions(entityPaths) {
        return this.getSpecificPaths(entityPaths, this.newPropRegexp);
    }

    getChanges(originPaths, origin, entity) {
        let diff = [];

        originPaths.forEach((path) => {
            const originProp = this.getProperty(origin, path);
            const draftProp = this.getProperty(entity, path);

            if (typeof originProp === 'function') {
                return;
            }

            if (originProp !== draftProp) {
                diff.push(path);
            }
        });

        return diff;
    }

    getJoinedPaths(obj) {
        return this.getPaths(obj).map(this.joinPath)
    }

    joinPath(path) {
        return path.join('.');
    }

    getPaths(obj, prefix = []) {
        if (!obj) {
            return [];
        }

       return Object.keys(obj).reduce((arr, key) => {
            const path = [...prefix, key];

            if (this.isIgnoredPath(this.joinPath(path))) {
                return arr;
            }

            const content = typeof obj[key] === 'object' ? this.getPaths(obj[key], path) : [path];
            return [...arr, ...content];
        }, []);
    }

    isIgnoredPath(path) {
        return this.getExcludeRegexArr().reduce((isIgnored, currentRegex) => {
            return currentRegex.test(path) ? true : isIgnored;
        }, false);
    }

    getExcludeRegexArr() {
        return this.ignoredPropertyPatterns;
    }

    getProperty(obj, path) {
        let current = obj;
        const parts = path.split('.');

        for (let i = 0; i < parts.length; i++) {
            if (!current[parts[i]]) {
                return null;
            }

            current = current[parts[i]];
        }

        return current;
    }

    getSpecificPaths(paths, regexp) {
        return paths.filter((path) => {
            if (regexp.test(path)) {
                return true;
            }
        });
    }
}
