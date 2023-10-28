require('@babel/register');

module.exports = (on, config) => {
    require('cypress-grep/src/plugin')(config)
    return config
};
