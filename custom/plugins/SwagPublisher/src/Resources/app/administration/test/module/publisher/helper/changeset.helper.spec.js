import ChangesetHelper from '../../../../src/module/publisher/helper/changeset.helper';

let helper;

describe('changeset.helper', () => {

    beforeEach(() => {
        helper = new ChangesetHelper();
    });

    it('detects deletions in the entity object', () => {
        const entity = {
            id: '1',
            foo: {
                id: '3'
            }
        };
        const origin = {
            id: '1',
            john: {
                id: '2',
                value: 'doe'
            },
            foo: {
                id: '3'
            }
        };

        const hasChanges = helper.hasUnsavedChanges(entity, origin);

        expect(hasChanges).toBeTruthy();
    });

    it('detects additions in the entity object', () => {
        const entity = {
            id: '1',
            john: {
                id: '2',
                _isNew: true
            }
        };
        const origin = {
            id: '1'
        };

        const hasChanges = helper.hasUnsavedChanges(entity, origin);

        expect(hasChanges).toBeTruthy();
    });

    it('detects changes in the entity object', () => {
        const entity = {
            id: '1',
            value: 'foo'
        };
        const origin = {
            id: '1',
            value: 'bar',
            foo: () => {}
        };

        const hasChanges = helper.hasUnsavedChanges(entity, origin);

        expect(hasChanges).toBeTruthy();
    });

    it('only retrieves valid and unignored paths', () => {
       const obj = {
           id: '1',
           john: null,
           extensions: {
               foo: 'bar'
           }
       };

       const paths = helper.getPaths(obj);

       expect(paths.length).toBe(1);
    });
});
