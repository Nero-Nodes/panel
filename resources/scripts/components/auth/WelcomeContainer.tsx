import React, { useState } from 'react';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import useFlash from '@/plugins/useFlash';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import discord from '@/api/auth/discord';
import { faAt, faCommentDots } from '@fortawesome/free-solid-svg-icons';
import { Link } from 'react-router-dom';
import WelcomeFormContainer from '@/components/auth/WelcomeFormContainer';

const WelcomeContainer = () => {
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const [ loading, setLoading ] = useState(false);

    const onSubmit = () => {
        clearFlashes();
        setLoading(true);

        console.log('Authenticating with Discord API');

        discord()
            .then(() => setLoading(false))
            .catch(error => {
                console.error(error);
                clearAndAddHttpError({ error });
            });
    };

    return (
        <WelcomeFormContainer css={tw`w-full flex`}>
            <div css={tw`mt-6`}>
                <Button size={'xlarge'} onSubmit={onSubmit} disabled={loading}>
                    <FontAwesomeIcon icon={faCommentDots}/> Login with Discord
                </Button>
            </div>
            <div css={tw`mt-6 mb-6`}>
                <Link to={'/auth/login/email'}>
                    <Button size={'xlarge'}>
                        <FontAwesomeIcon icon={faAt}/> Login with Email
                    </Button>
                </Link>
            </div>
        </WelcomeFormContainer>
    );
};

export default WelcomeContainer;
