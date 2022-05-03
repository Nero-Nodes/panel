import React, { useState } from 'react';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import useFlash from '@/plugins/useFlash';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import discordLogin from '@/api/auth/discord';
import { faUserLock } from '@fortawesome/free-solid-svg-icons';
import { Link } from 'react-router-dom';
import WelcomeFormContainer from '@/components/auth/WelcomeFormContainer';

const WelcomeContainer = () => {
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const [ loading, setLoading ] = useState(false);

    const login = () => {
        clearFlashes();
        setLoading(true);

        discordLogin()
            .then((data) => {
                if (!data) return clearAndAddHttpError({ error: 'Discord auth failed. Please try again.' });
                window.location.href = data;
            })
            .then(() => setLoading(false))
            .catch(error => {
                console.error(error);
                clearAndAddHttpError({ error });
            });
    };

    return (
        <WelcomeFormContainer css={tw`w-full flex`}>
            <div css={tw`flex flex-col md:h-full`}>
                <h3 css={tw`font-sans text-2xl text-center text-neutral-500 font-normal`}>Please sign in.</h3>
                <div css={tw`mt-6`}>
                    <Button type={'button'} size={'xlarge'} onClick={() => login()} disabled={loading}>
                        <FontAwesomeIcon icon={faUserLock}/> Log In
                    </Button>
                </div>
                <div css={tw`mt-6 text-center`}>
                    <Link
                        to={'/auth/login/email'}
                        css={tw`text-xs text-neutral-500 tracking-wide no-underline uppercase hover:text-neutral-600`}
                    >
                        Login with Email
                    </Link>
                </div>
            </div>
        </WelcomeFormContainer>
    );
};

export default WelcomeContainer;
